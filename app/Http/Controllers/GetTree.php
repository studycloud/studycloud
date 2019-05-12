<?php

namespace App\Http\Controllers;

use App\User;
use App\Topic;
use App\Resource;
use App\Academic_Class;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use App\Helpers\NodesAndConnections;
use App\Repositories\ClassRepository;
use App\Repositories\TopicRepository;
use Illuminate\Support\Facades\Route;
use App\Repositories\ResourceRepository;
use Illuminate\Database\Eloquent\Collection;

class GetTree extends Controller
{
	/**
	 * The nodes and connections for this tree.
	 *
	 * @var Collection
	 */
	protected $tree;

	/**
	 * The type of tree we are dealing with.
	 * @var string
	 */
	protected $type;

	/**
	 * The name of the model class for the tree item.
	 * @var string
	 */
	protected $model_name;

	/**
	 * The name of the repository class we will use.
	 * @var string
	 */
	protected $repo;


	public function __construct(Request $request)
	{
		// are we dealing with the topic tree or the class tree?
		// we can check the route name to figure it out
		if ($request->route()->named('tree.topic'))
		{
			$this->type = "topic";
			$this->model_name = Topic::class;
			$this->repo = TopicRepository::class;
		}
		elseif ($request->route()->named('tree.class'))
		{
			$this->type = "class";
			$this->model_name = Academic_Class::class;
			$this->repo = ClassRepository::class;
		}
	}

	/**
	 * a wrapper for show() that parses the query string. This
	 * function is automatically invoked by Laravel when the 
	 * controller is called.
	 * @return Collection 	the return value of show()
	 */
	public function __invoke(Request $request)
	{
		// first, validate the input
		$validated = $request->validate([
			'id' => [
				'nullable',
				'integer',
				Rule::in(
					$this->model_name::pluck('id')->push(0)->toArray()
				)
			],
			'levels_up' => 'nullable|integer|min:0',
			'levels_down' => 'nullable|integer|min:0',
		]);

		// now, retrieve the input
		$node_id = $request->input('id');
		$node_id = is_null($node_id) ? 0 : $node_id;
		$up = $request->input('levels_up');
		$down = $request->input('levels_down');

		return $this->show($node_id, $up, $down);
	}

	/**
	 * converts a portion of the tree to JSON for traversal by the JavaScript team
	 * @param	integer		$node_id		the id of the current node in the tree; defaults to the root of the tree, which has an id of 0
	 * @param	int|null	$levels_up		the number of ancestor levels of the tree to return; defaults to infinity
	 * @param	int|null	$levels_down	the number of descendant levels of the tree to return; defaults to infinity
	 * @return	Collection					the nodes and connections of the target portion of the tree
	 */
	public function show($node_id = 0, int $levels_up = null, int $levels_down = null)
	{
		// first, get the required data
		$this->getNodes($node_id, $levels_up, $levels_down);
		// then, convert the data to the nodes/connections format
		$node_ids = $this->convertFormat();

		// get the converted resources of each node in the tree
		$resources = $this->getResourceNodes($node_ids);

		// add the resources and connections to the tree
		$this->tree->put("nodes", $this->tree["nodes"]->merge($resources["nodes"]));
		$this->tree->put("connections", $this->tree["connections"]->merge($resources["connections"]));

		// return the tree data: a collection of the resulting lists of nodes and connections
		return $this->tree;
	}

	/**
	 * get the raw nodes and connections data
	 * @param	integer		$node_id		the id of the current node in the tree; defaults to the root of the tree, which has an id of 0
	 * @param	int|null	$levels_up		the number of ancestor levels of the tree to return; defaults to infinity
	 * @param	int|null	$levels_down	the number of descendant levels of the tree to return; defaults to infinity
	 */
	private function getNodes($node_id, $levels_up, $levels_down)
	{
		$root = $this->model_name::getRoot();
		$node = null;
		if ($node_id != 0)
		{
			$node = $this->model_name::find($node_id);
		}
		// get the ancestors and descendants of this node in a flat collection
		$this->tree = (new $this->repo)->ancestors($node, $levels_up, $root)->merge(
			(new $this->repo)->descendants($node, $levels_down, $root)
		);
		// add the current node to the data
		// but use the root if the current node is null
		$this->tree->prepend(collect(is_null($node) ? $root : $node));
	}

	/**
	 * convert the tree data to the nodes/connections format
	 * @return Collection	$node_ids	the IDs of each node in the tree
	 */
	private function convertFormat()
	{
		// convert the data to the nodes/connections format
		$this->tree = NodesAndConnections::convertTo($this->tree);
		// get all of the node_ids
		$node_ids = $this->tree["nodes"]->pluck("id");
		// get each node in the tree and process it
		$this->tree["nodes"]->transform(function($node)
		{
			return $this->processNode($node);
		});
		// get each connection in the tree and process it
		$this->tree["connections"]->transform(function($connection)
		{
			return $this->processConnection($connection);
		});
		return $node_ids;
	}

	/**
	 * get the resources of each node in the tree in the nodes/connections format
	 * @param  Collection	$node_ids	the IDs of each node in the tree
	 * @return Collection				the resources
	 */
	private function getResourceNodes($node_ids)
	{
		// first, get the resources of each node
		if ($this->type == "topic")
		{
			$resources = ResourceRepository::getByTopics($node_ids);
		}
		elseif ($this->type == "class")
		{
			$resources = ResourceRepository::getByClasses($node_ids);
		}

		// convert the resources into the nodes/connections format
		$resources = NodesAndConnections::convertTo($resources);

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

		return $resources;
	}

	/**
	 * process this node so that it shows the correct 
	 * attributes when the request is returned to the user
	 * @param  Collection 	$node 	the node to process
	 * @return Collection       	the processed node
	 */
	private function processNode($node)
	{
		// add a 't' to the beginnning of the id
		$node->put('id', 't'.$node['id']);
		// add author name; it's more useful than the author id
		// $node->put('author_name', User::find($node->get('author_id'))->name());
		// send only the attributes that we need
		if ($this->type == 'class')
		{
			$node = $node->only('id', 'name', 'author_id', 'status', 'created_at', 'updated_at');
		}
		else
		{
			$node = $node->only('id', 'name', 'author_id', 'created_at', 'updated_at');
		}
		return $node;
	}

	/**
	 * process this connection so that it shows the correct 
	 * attributes when the request is returned to the user
	 * @param  Collection 	$connection 	the connection to process
	 * @return Collection        			the processed pivot as a collection
	 */
	private function processConnection($connection)
	{
		// make "parent_id" into "source" and "<node>_id" into "target"
		// also add 't' to the id's
		$connection->prepend('t'.$connection->pull($this->type.'_id'), 'target');
		$connection->prepend('t'.$connection->pull('parent_id'), 'source');
		return $connection;
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
		// add author name; it's more useful than the author id
		// $node->put('author_name', User::find($node->get('author_id'))->name());
		// send only the attributes that we need
		$node = $node->only('id', 'name', 'author_id', 'created_at', 'updated_at');
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
		// make "<node>_id" into "source" and "resource_id" into "target"
		// also add 't' to the <node>_id and 'r' to the resource_id
		$connection->prepend('r'.$connection->pull('resource_id'), 'target');
		$connection->prepend('t'.$connection->pull($this->type.'_id'), 'source');
		return $connection;
	}
}