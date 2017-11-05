<?php

namespace App\Http\Controllers;

use App\Topic;
use App\TopicParent;
use App\Resource;
use Illuminate\Http\Request;

class TreeController extends Controller
{
	protected $nodes;

	protected $connections;


    public function toJson($topic_id = 0, $levels = 0)
    {
    	// initialize our data members to empty collections
    	$this->nodes = collect();
    	$this->connections = collect();

    	// if the topic_id is 0, the user wants the root of the tree
    	if ($topic_id == 0)
    	{
    		Topic::getTopLevelTopics()->each(
    			function($topic)
    			{
    				global $levels;
    				$this->nodes->push($topic->makeVisible('unique_id')->makeHidden('id'));
					$this->addNodes($topic->descendants($levels-1));
    			}
    		);
    	}
    	else
    	{
	    	// get the current topic the tree is pointing to
		    $topic = Topic::find($topic_id);
	    	// add the descendants of the current topic to the list of nodes
			$this->addNodes($topic->descendants($levels));
		}

		// return a collection of the resulting lists of nodes and connections
		return collect(["nodes" => $this->nodes->unique(), "connections" => $this->connections]);
    }

    private function addNodes($nodes)
    {
    	// add each node and its connections to the $nodes and $connections data members
    	$nodes->each(
    		function($node)
    		{
				$this->nodes->push(
					// process this node so that it shows the correct attributes when the request is returned to the user
					$node->makeHidden('pivot')->makeVisible('unique_id')->makeHidden('id')
				);
	    		$this->connections->push(
	    			// get the pivot object of this node
	    			// this represents a connection
	    			$node->pivot
	    		);

	    		// if this node is a topic, we'll also have to add it's resources to the list of nodes
    			if (is_a($node, "App\Topic"))
    			{
    				$this->addNodes($node->getResources());
    			}
    		}
    	);
    }
}
