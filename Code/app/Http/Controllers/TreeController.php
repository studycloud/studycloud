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


    public function getJSONData($topic_id, $levels = 0)
    {
    	$topic = Topic::find($topic_id);

    	$this->nodes = collect();
    	$this->connections = collect();

		$this->topicNodes($topic, $levels);
		
		return collect(["nodes" => $this->nodes, "connections" => $this->connections]);
    }

    private function topicNodes(Topic $topic, $levels)
    {
		return $this->addNodes($topic->descendants($levels));
    }

    private function resourceNodes(Topic $topic)
    {
		return $this->addNodes($topic->getResources());
    }

    private function addNodes($nodes)
    {
    	$nodes->each(
    		function($node)
    		{
				$this->nodes->push(
					$node->makeHidden('pivot')->makeVisible('unique_id')->makeHidden('id')
				);
	    		$this->connections->push(
	    			$node->pivot
	    		);

    			if (is_a($node, "App\Topic"))
    			{
    				$this->resourceNodes($node);
    			}
    		}
    	);
    }
}
