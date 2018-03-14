<?php

namespace App\Repositories;

use App\Topic;

class TopicRepository
{
	protected $nodes;

	protected $connections;

	/**
	 * Finds the topics that are at the root (very top) of the tree.
	 *
	 * @return Illuminate\Database\Eloquent\Collection
	 */
	public static function getTopLevelTopics()
	{
		return Topic::whereNotExists(function ($query)
			{
				$query->select('topic_id')->distinct()->from('topic_parent')->whereRaw('topic_parent.topic_id = topics.id');
			}
		)->get();
	}

	/**
	 * get the descendants of a topic in a flat collection
	 * this is a wrapper for the addDescendants function
	 * @param  integer $topic_id the id of the current topic in the tree; defaults to the root of the tree, which has an id of 0
	 * @param  int $levels the number of levels of descendants to get; returns all if $levels is not specified or is less than 0
	 * @return Illuminate\Database\Eloquent\Collection
	 */
	public function descendants(int $topic_id = 0, int $levels = null)
	{
		// initialize our data members to empty collections
		$this->nodes = collect();
		$this->connections = collect();

		// get the current topic the tree is pointing to
		// if the topic_id is 0, the user wants the root of the tree
		$topic = null;
		if ($topic_id != 0)
		{
			$topic = Topic::find($topic_id);
		}

		$this->addDescendants($topic, $levels);

		return collect(["nodes" => $this->nodes, "connections" => $this->connections]);
	}


	/**
	 * load the descendants of a topic in a flat collection
	 * into the nodes and connections data members
	 * @param  App\Topic the current topic in the tree; defaults to the root of the tree
	 * @param  int $levels the number of levels of descendants to get; returns all if $levels is not specified or is less than 0
	 */
	private function addDescendants(Topic $topic = null, int $levels = null)
	{
		// base case: $levels == 0
		if ($levels != 0 || is_null($levels))
		{
			if (is_null($topic))
			{
				$children = self::getTopLevelTopics();
			}
			else
			{
				$children = $topic->children()->get();
			}
			foreach ($children as $child) {
				// check whether the node has been added already
				$addedAlready = $this->nodes->pluck('id')->contains($child->id);
				// add the node and its connections to the corresponding data members
				$this->add($child);
				// this is a memoization check
				// it prevents us from calling addDescendants on any topic more than once
				if (!$addedAlready)
				{
					// RECURSION!
					$this->addDescendants($child, $levels - 1);
				}
			}
		}
	}

	/**
	 * get the ancestors of a topic in a flat collection
	 * this is a wrapper for the addAncestors function
	 * @param  integer $topic_id the id of the current topic in the tree; defaults to the root of the tree, which has an id of 0
	 * @param  int $levels the number of levels of ancestors to get; returns all if $levels is not specified or is less than 0
	 * @return Illuminate\Database\Eloquent\Collection
	 */
	public function ancestors(int $topic_id = 0, int $levels = null)
	{
		// initialize our data members to empty collections
		$this->nodes = collect();
		$this->connections = collect();

		// get the current topic the tree is pointing to
		// if the topic_id is 0, the user wants the root of the tree
		$topic = null;
		if ($topic_id != 0)
		{
			$topic = Topic::find($topic_id);
		}

		$this->addAncestors($topic, $levels);
		
		return collect(["nodes" => $this->nodes, "connections" => $this->connections]);
	}

	/**
	 * get the parents of each parent of each parent (etc) of this topic in a flat collection
	 * @param  App\Topic the current topic in the tree; defaults to the root of the tree
	 * @param  int $levels the number of levels of ancestors to get; returns all if $levels is not specified or is less than 0
	 * @return Illuminate\Database\Eloquent\Collection
	 */
	private function addAncestors(Topic $topic = null, int $levels = null)
	{
		// base case: $levels == 0
		if ($levels != 0 || is_null($levels))
		{
			if (is_null($topic))
			{
				$parents = collect();
			}
			else
			{
				$parents = $topic->parents()->get();
			}
			foreach ($parents as $parent) {
				// check whether the node has been added already
				$addedAlready = $this->nodes->pluck('id')->contains($parent->id);
				// add the node and its connections to the corresponding data members
				$this->add($parent);
				// this is a memoization check
				// it prevents us from calling addAncestors on any topic more than once
				if (!$addedAlready)
				{
					// RECURSION!
					$this->addAncestors($child, $levels - 1);
				}
			}
		}
	}

	/**
	 * adds the given node and any connections to the appropriate $nodes and $connections collections
	 * @param \Illuminate\Database\Eloquent\Collection $nodes the nodes to add
	 */
	private function add($node)
	{
		// check whether the node has been added already before adding it
		if (!$this->nodes->pluck('id')->contains($node->id))
		{
			$this->nodes->push(
				collect($node)
			);
		}

		// add any connections the node may have
		if (!is_null($node->pivot))
		{
			$this->connections->push(
				collect($node->pivot)
			);
		}
	}

}