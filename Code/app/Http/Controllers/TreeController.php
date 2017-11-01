<?php

namespace App\Http\Controllers;

use App\Topic;
use App\TopicParent;
use App\Resource;
use Illuminate\Http\Request;

class TreeController extends Controller
{
    public function getJSONData($topic_id, $levels = 0)
    {
    	$curr_topic = Topic::find($topic_id);
		$tree_data = collect();
		$topic_nodes = $curr_topic->descendants($levels);
		$topic_nodes->transform(function($topic){
			$topic = $topic->makeHidden('pivot')->makeVisible('unique_id')->makeHidden('id');
			return $topic;
		});
		// $nodes = $topic_nodes;
		$tree_data->put("nodes", $topic_nodes);
		$tree_data->put("connections", TopicParent::all());
		return $tree_data;
    }
}
