<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Topic extends Model
{
    /**
     * Holds an array representing the tree, with keys as 
     * parent_id's and values as an array of children_id's.
     * Keys that have empty arrays as values represent topics
     * at the bottom of the tree (i.e. which don't have any
     * children).
     * @var array
     */
    protected static $tree = [];

    /**
     * a memoization helper for any functions that might need it
     * @var array
     */
    protected static $memoize_helper = [];

    /**
     * Makes an array representing the topic tree.
     *
     * @param array the current branch to work on
     * @return void
     */
    protected static function generateTree(&$node)
    {
    	// each node contains an array of topic_id's, each of which must be converted to pairs of parent_id's each mapped to a different array of children_id's
    	
    	// let's create a temporary array that can save our changes to the elements of node
    	// later, we'll copy the temporary node into the new node
    	// we have to do this because the foreach loop doesn't allow changes to the node var
        $temp_node = [];
        foreach ($node as $branch_value) { // $branch_value is the new parent_id
        	// if we haven't yet found the children of this topic, we must do so
        	if (!array_key_exists($branch_value, self::$memoize_helper))
        	{
        		// store an array of childre_id's, mapped to each parent_id
	            $temp_node[$branch_value] = Topic::find($branch_value)->children()->get()->pluck('id')->all();
	            // now, store the array of children_id's in the memoize helper for later
	            self::$memoize_helper[$branch_value] = $temp_node[$branch_value];
	        }
	        else
	        {
	        	// we already have the children, so let's get it from the memoize helper to save some time
	        	$temp_node[$branch_value] = self::$memoize_helper[$branch_value];
	        }
	        // recursive call! pass the array of children_id's
	        self::generateTree($temp_node[$branch_value]);
        }
        // trade our old node for the updated one
        // note that this will actually persist the changes to the static tree variable, since the node was passed by reference
        $node = $temp_node;
    }

    /**
     * Returns the updated tree.
     *
     * @return array $tree
     */
    public static function getTree()
    {
    	// we must prep the tree with the top level topics before we can call it, since that is what it expects to accept
        self::$tree = Topic::getTopLevelTopics()->pluck('id')->all();
        self::generateTree(self::$tree);
        // make sure to clear the memoize helper so that it can be used later
        self::$memoize_helper = [];
        return self::$tree;
    }

	/**
	 * returns all topics that have this topic as their parent
	 */
	public function children()
	{
		return $this->belongsToMany(Topic::class, 'topic_parent', 'parent_id',  'topic_id');
	}

	/**
	 * returns all topics for which this topic is a child
	 */
	public function parents()
	{
		return $this->belongsToMany(Topic::class, 'topic_parent', 'topic_id',  'parent_id');
	}

    /**
     * Finds the topics that are at the root (very top) of the tree.
     *
     * @return Illuminate\Database\Eloquent\Collection
     */
	public static function getTopLevelTopics()
	{
		return self::whereNotIn('id', TopicParent::pluck('topic_id')->all())->get();
	}
}