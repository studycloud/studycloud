<?php

namespace App\Http\Controllers;

use App\Topic;
use App\TopicParent;
use App\Resource;
use App\Repositories\TopicRepository;
use App\Repositories\ResourceRepository;
use App\Helpers\NodesAndConnections;
// use Barryvdh\Debugbar\Facade as Debugbar;
use Illuminate\Http\Request;
use Illuminate\Database\Eloquent\Collection;

class GetTopicTree extends Controller
{
	/**
	 * The nodes and connections for this tree.
	 *
	 * @var Collection
	 */
	protected $tree;


	/**
	 * a wrapper for show() that parses the query string. This
	 * function is automatically invoked by Laravel when the 
	 * controller is called.
	 * @return Collection 	the return value of show()
	 */
	public function __invoke(Request $request)
	{
		$topic_id = $request->input('topic');
		if ($topic_id == "")
		{
			$topic_id = null;
		}
		$up = $request->input('levels_up');
		if ($up == "")
		{
			$up = null;
		}
		$down = $request->input('levels_down');
		if ($down == "")
		{
			$down = null;
		}
		return $this->show($topic_id, $up, $down);
	}

	/**
	 * converts a portion of the tree to JSON for traversal by the JavaScript team
	 * @param	integer		$topic_id		the id of the current topic in the tree; defaults to the root of the tree, which has an id of 0
	 * @param	int|null	$levels_up		the number of ancestor levels of the tree to return; defaults to infinity
	 * @param	int|null	$levels_down	the number of descendant levels of the tree to return; defaults to infinity
	 * @return	Collection					the nodes and connections of the target portion of the tree
	 */
	public function show($topic_id = 0, int $levels_up = null, int $levels_down = null)
	{
		$topic = null;
		if ($topic_id != 0)
		{
			$topic = Topic::find($topic_id);
		}
		// get the ancestors and descendants of this topic in a flat collection
		$this->tree = (new TopicRepository)->ancestors($topic, $levels_up)->merge(
			(new TopicRepository)->descendants($topic, $levels_down)
		);
		// convert the data to the nodes/connections format
		$this->tree = NodesAndConnections::convertTo($this->tree);
		// get all of the topic_ids
		$topic_ids = $this->tree["nodes"]->pluck("id");
		// get each topic in the tree and process it
		$this->tree["nodes"]->transform(function($node)
		{
			return $this->processTopic($node);
		});
		// get each connection in the tree and process it
		$this->tree["connections"]->transform(function($connection)
		{
			return $this->processTopicConnection($connection);
		});
		
		// get the resources of each topic in the nodes/connections format
		$resources = NodesAndConnections::convertTo(ResourceRepository::getByTopics($topic_ids));
		// get each resource in the tree and transform it
		$resources["nodes"]->transform(function($node)
		{
			return $this->processResource($node);
		});
		// get each connection in the tree and transform it
		$resources["connections"]->transform(function($connection)
		{
			return $this->processResourceConnection($connection);
		});
		// add the resources and connections to the tree
		$this->tree->put("nodes", $this->tree["nodes"]->merge($resources["nodes"]));
		$this->tree->put("connections", $this->tree["connections"]->merge($resources["connections"]));

		// return the tree data: a collection of the resulting lists of nodes and connections
		return $this->tree;
	}

	/**
	 * process this node so that it shows the correct 
	 * attributes when the request is returned to the user
	 * @param  Collection 	$node 	the node to process
	 * @return Collection       	the processed node
	 */
	private function processTopic($node)
	{
		// add a 't' to the beginnning of the id
		$node->put('id', 't'.$node['id']);
		return $node;
	}

	/**
	 * process this connection so that it shows the correct 
	 * attributes when the request is returned to the user
	 * @param  Collection 	$connection 	the connection to process
	 * @return Collection        			the processed pivot as a collection
	 */
	private function processTopicConnection($connection)
	{
		// make "parent_id" into "source" and "topic_id" into "target"
		// also add 't' to the id's
		$connection->prepend('t'.$connection->pull('topic_id'), 'target');
		$connection->prepend('t'.$connection->pull('parent_id'), 'source');
		return $connection;
	}

	/**
	 * adds the given node and any connections to the appropriate $nodes and $connections collections
	 * @param Collection 	$nodes 	the nodes to add
	 */
	private function addResource($node)
	{
		// double check that this node hasn't already been added to $this->tree.get("nodes"). handles duplicate resources
		if (!$this->tree["nodes"]->pluck('target')->contains('r'.$node["id"]))
		{
			$this->tree["nodes"]->push(
				$this->processResource($node)
			);
		}

		if (!is_null($node["pivot"]))
		{
			$this->tree.get("connections")->push(
				$this->processResourceConnectionConnection($node["pivot"])
			);
		}
	}

	/**
	 * process this node so that it shows the correct 
	 * attributes when the request is returned to the user
	 * @param  Collection 	$node 	the node to process
	 * @return Collection       	the processed node
	 */
	private function processResource($node)
	{
		// add an 'r' to the beginnning of the id
		$node->put('id', 'r'.$node['id']);
		return $node;
	}

	/**
	 * process this connection so that it shows the correct 
	 * attributes when the request is returned to the user
	 * @param  Collection $connection 	the connection to process
	 * @return Collection        		the processed pivot as a collection
	 */
	private function processResourceConnection($connection)
	{
		// make "topic_id" into "source" and "resource_id" into "target"
		// also add 't' to the topic_id and 'r' to the resource_id
		$connection->prepend('r'.$connection->pull('resource_id'), 'target');
		$connection->prepend('t'.$connection->pull('topic_id'), 'source');
		return $connection;
	}
}