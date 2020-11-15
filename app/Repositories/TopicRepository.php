<?php

namespace App\Repositories;

use App\Topic;
use App\Helpers\NestedArrays;
use Illuminate\Support\Collection;

class TopicRepository
{
	protected $memoize = [];

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
	 * @param  App\Topic the current topic in the tree; defaults to the root of the tree
	 * @param  int $levels the number of levels of descendants to get; returns all if $levels is not specified or is less than 0
	 * @return Illuminate\Database\Eloquent\Collection
	 */
	public function descendants(Topic $topic = null, int $levels = null, $root = null)
	{
		$tree = collect();
		
		// base case: $levels == 0
		// also do a memoization check to prevent us from
		// executing a query for a topic that we've already found
		if (
			($levels != 0 || is_null($levels)) &&
			(is_null($topic) || !in_array($topic->id, $this->memoize))
		)
		{
			if (is_null($topic))
			{
				$children = self::getTopLevelTopics();

				if (!is_null($root))
				{
					foreach ($children as $child) {  //iterates through each top level topic
						// add a pivot element to each topic
						$child->pivot = collect(["parent_id" => $root['id'], "topic_id" => $child["id"]]);
					}
				}
			}
			else
			{
				$children = $topic->children()->get();
				// add the topic id to the list of topics that have already been called
				array_push($this->memoize, $topic->id);
			}

			foreach ($children as $child) {
				// add the child to the tree
				$tree->push(collect($child));
				$tree = $tree->merge(
					// RECURSION!
					$this->descendants($child, is_null($levels) ? null : $levels - 1, $root)
				);
			}
		}
		return $tree;
	}

	/**
	 * get the ancestors of a topic in a flat collection
	 * @param  App\Topic the current topic in the tree; defaults to the root of the tree
	 * @param  int $levels the number of levels of ancestors to get; returns all if $levels is not specified or is less than 0
	 * @return Illuminate\Database\Eloquent\Collection
	 */
	public function ancestors(Topic $topic = null, int $levels = null, $root = null)
	{
		$tree = collect();

		// base case: $levels == 0
		// also do a memoization check to prevent us from
		// executing a query for a topic that we've already found
		if (
			($levels != 0 || is_null($levels)) &&
			(is_null($topic) || !in_array($topic->id, $this->memoize))
		)
		{
			if (is_null($topic))
			{
				$parents = collect();
			}
			else
			{
				$parents = $topic->parents()->get();
				// add the topic id to the list of topics that have already been called
				array_push($this->memoize, $topic->id);
				// add the root and its connection to this topic,
				// if this topic doesn't have any parents
				if ($parents->isEmpty() && !is_null($root))
				{
					$root->put("pivot", collect(["parent_id" => $root['id'], "topic_id" => $topic['id']]));
					$tree->push($root);
				}
			}

			foreach ($parents as $parent) {
				// add the parent to the tree
				$tree->push(collect($parent));
				$tree = $tree->merge(
					// RECURSION!
					$this->ancestors($parent, is_null($levels) ? null : $levels - 1, $root)
				);
			}
		}
		return $tree;
	}

	/**
	 * given a portion of the tree, check to see whether $descendant_topic_id is a descendant of $topic_id
	 * @param  int 			$topic_id           	the ancestor topic
	 * @param  int  		$descendant_topic_id   	the descendant to search for
	 * @param  Collection 	$disallowed_topics 		a portion of the tree to traverse, as a collection of connections
	 * @return boolean                  			whether $descendant_topic_id is an descendant of $topic
	 */
	public static function isDescendant($topic_id, $descendant_topic_id, $disallowed_topics)
	{
		// base case: descendant_topic is an descendant of topic if they are the same
		if ($topic_id == $descendant_topic_id)
		{
			return true;
		}
		// get the topic collections in $disallowed_topics with parent_ids equal to $topic_id
		$topics = $disallowed_topics->where('parent_id', $topic_id);
		$isDescendant = false;
		// call isDescendant() with each of the topics
		// and then OR all of the results together to get a final value
		foreach ($topics as $topic)
		{
			// is the parent of this $topic a descendant of $descendant_topic_id?
			$isDescendant = $isDescendant || self::isDescendant($topic['topic_id'], $descendant_topic_id, $disallowed_topics);
		}
		return $isDescendant;
	}

	/**
	 * given a portion of the tree, check to see whether $ancestor_topic_id is an ancestor of $topic_id
	 * @param  int 			$topic_id 			the descendant topic
	 * @param  int 			$ancestor_topic_id 	the ancestor to search for
	 * @param  Collection  	$disallowed_topics 	a portion of the tree to traverse, as a collection of connections
	 * @return boolean                  		whether $ancestor_topic_id is an ancestor of $topic
	 */
	public static function isAncestor($topic_id, $ancestor_topic_id, $disallowed_topics)
	{
		// base case: ancestor_topic is an ancestor of topic if they are the same
		if ($topic_id == $ancestor_topic_id)
		{
			return true;
		}
		// get the connections in $disallowed_topics with topic_ids equal to $topic_id
		$topics = $disallowed_topics->where('topic_id', $topic_id);
		$isAncestor = false;
		// call isAncestor() with each of the topics' parents
		// (ie ask whether $ancestor_topic_id is an ancestor of each topic's parent)
		// and then OR all of the results together to get a final value
		foreach ($topics as $topic)
		{
			// is the parent of this $topic an ancestor of $ancestor_topic_id?
			$isAncestor = $isAncestor || self::isAncestor($topic['parent_id'], $ancestor_topic_id, $disallowed_topics);
		}
		return $isAncestor;
	}

	/**
	 * retrieve the depths of each topic in $connections
	 * The depths of a topic are defined as the lengths of the shortest paths
	 * between each of the topic's parents and the root of the tree. Thus,
	 * each topic will have n depths (where n is the number of parents of the topic).
	 * @param  Collection	$connections	the connections from topics for which we want depths, as a Collection of topic/parent Collections; the topics without a parent are those at depth of $depth+1
	 * @param  int			$depth			the depth of the root of this subtree
	 * @return Collection	$depths			a collection of depths where the keys are the IDs of the topics and the values are an array containing all their depths
	 */
	public static function depths(Collection $connections, $depth=0)
	{
		// get the topics that are directly underneath the root of this subtree
		$top_topics = $connections->pluck('parent_id')->unique()->diff($connections->pluck('topic_id'))->values();
		// add connections for the top_topics to $connections
		$connections = $top_topics->map(
			function($topic)
			{
				return collect(['topic_id'=>$topic, 'parent_id'=>null]);
			}
		)->merge($connections->toBase());
		// group connections by topic and get a collection to work with in our algorithm
		$working = $connections->groupBy('topic_id')->map->count()->map(
			function ($parent_count, $topic)
			{
				return collect([
					'topic_id'=>$topic,
					'parents'=>$parent_count,
					'depths'=>collect([]),
					'complete'=>false,
					'saw'=>false
				]);
			}
		)->keyBy('topic_id');
		// initialize $level for use within the while loop
		$level = collect([null]);
		// while we haven't finished finding the depths of all the topics
		while ($working->whereStrict('complete', true)->count() != $working->count())
		{
			$depth += 1;
			// iterate down the tree, each time working with all topics at this level of the tree
			// and retrieving all topics at the next level
			// (ie a depth-first search)
			$level = $level->map(
				function($parent) use ($connections, $working, $depth)
				{
					// get all children of the current parent topic
					$children = $connections->whereStrict('parent_id', $parent)->pluck('topic_id');
					// if we haven't looked for the children of $parent before (or it is the root, which doesn't have a parent)
					if (!$working->has($parent) || !$working[$parent]['saw'])
					{
						// record that we've seen the children of this parent now
						$working->has($parent) && $working[$parent]['saw'] = true;
						// foreach of the children...
						foreach ($children as $child)
						{
							// record the current depth
							$working[$child]['depths']->push($depth);
							// if we've found all of the depths, mark it as complete
							if (count($working[$child]['depths']) == $working[$child]['parents'])
							{
								$working[$child]['complete'] = true;
							}
						}
					}
					return $children;
				}
			)->flatten();
		}
		// return simple array from the working collection
		return $working->pluck('depths', 'topic_id');
	}

	/**
	 * print the descendants of $topic as an ascii tree
	 * @param  Topic|int  $topic the topic whose descendants we'd like to print
	 */
	public static function printAsciiDescendants($topic)
	{
		if (is_int($topic))
		{
			$topic = Topic::find($topic);
		}

		echo NestedArrays::convertToAscii(NestedArrays::topicDescendants($topic));
	}

	/**
	 * print the ancestors of $topic as an ascii tree
	 * @param  Topic|int  $topic the topic whose ancestors we'd like to print
	 */
	public static function printAsciiAncestors($topic)
	{
		if (is_int($topic))
		{
			$topic = Topic::find($topic);
		}

		echo NestedArrays::convertToAscii(NestedArrays::topicAncestors($topic));
	}

	/**
	 * convenience function for printing both ancestors and descendants of $topic as an ascii tree
	 * @param  Topic|int|null  $topic the topic whose ancestors and descendants we'd like to print
	 */
	public static function asciiTree($topic=null)
	{
		// handle null or 0 as the $topic
		if (!$topic)
		{
			self::getTopLevelTopics()->pluck('id')->each(
				function($topic)
				{
					echo self::printAsciiDescendants($topic);
				}
			);
			return;
		}

		if (is_int($topic))
		{
			$topic = Topic::find($topic);
		}

		echo "DESCENDANTS\n";
		self::printAsciiDescendants($topic);
		echo "\nANCESTORS\n";
		self::printAsciiAncestors($topic);
	}
}