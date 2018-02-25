<?php

namespace App\Repositories;

use App\Topic;

class TopicRepository
{
	protected $nodes;

	protected $connections;

	/**
	 * get the descendants of a topic in a flat collection
	 * this is a wrapper for the addDescendants function
	 * @param  integer $topic_id the id of the current topic in the tree; defaults to the root of the tree, which has an id of 0
	 * @param  int $levels the number of levels of descendants to get; returns all if $levels is not specified or is less than 0
	 * @return Illuminate\Database\Eloquent\Collection
	 */
	public function descendants($topic = null, $levels = null)
	{

	}


	/**
	 * load the descendants of a topic in a flat collection
	 * into the nodes and connections data members
	 * @param  integer $topic_id the id of the current topic in the tree; defaults to the root of the tree, which has an id of 0
	 * @param  int $levels the number of levels of descendants to get; returns all if $levels is not specified or is less than 0
	 */
	private function addDescendants($topic = null, $levels = null)
	{
		// base case: $levels == 0
		if ($levels != 0 || is_null($levels))
		{
			if (is_null($topic))
			{
				$children = Topic::getTopLevelTopics();
			}
			else
			{
				$children = $topic->children()->get();
			}
			foreach ($children as $child) {
				// this is a memoization check
				// it prevents us from calling addDescendants on any topic more than once
				if (!$this->nodes->pluck('target')->contains($child->target))
				{
					$this->add($child);
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
	public function ancestors($topic = null, $levels = null)
	{
		
	}

	/**
	 * get the parents of each parent of each parent (etc) of this topic in a flat collection
	 * @return Illuminate\Database\Eloquent\Collection
	 */
	private function addAncestors()
	{
		// $parents = $this->parents()->get();
		// $ancestors = $parents;
		// foreach ($parents as $parent) {
		// 	$ancestors = $ancestors->merge($parent->ancestors());
		// }
		// return $ancestors;
	}

	/**
	 * adds the given node and any connections to the appropriate $nodes and $connections collections
	 * @param \Illuminate\Database\Eloquent\Collection $nodes the nodes to add
	 */
	private function add($node)
	{
		// double check that this node hasn't already been added to $this->nodes. handles duplicate resources
		if (!$this->nodes->pluck('target')->contains($node->target))
		{
			$this->nodes->push(
				$this->processNode($node)
			);
		}

		if (!is_null($node->pivot))
		{
			$this->connections->push(
				$this->processPivot($node->pivot)
			);
		}

		// if this node is a topic, we'll also have to add it's resources to the list of nodes
		if (is_a($node, "App\Topic"))
		{
			$resources = $node->resources()->get();
			foreach ($resources as $resource) {
				$this->add($resource);
			}
			//recursion sucks - amp
		}
	}


}