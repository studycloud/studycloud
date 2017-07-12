<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Topic extends Model
{
    /**
     * The accessors to append to the model's array form.
     *
     * @var array
     */
	protected $appends = ['children'];

	/**
	 * @return \Illuminate\Database\Eloquent\Collection
	 */
    public function getChildrenAttribute()
    {
    	return $this->children()->get();
    }

    /**
     * Returns the updated tree.
     *
     * @return \Illuminate\Database\Eloquent\Collection $tree
     */
    public static function getTree()
    {
        return self::getTopLevelTopics();
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