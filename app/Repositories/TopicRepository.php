<?php

namespace App\Repositories;

use App\Topic;
use App\Helpers\NestedArrays;

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
						$child->pivot = collect(["parent_id" => $root['id'], "topic_id" => $child["id"]]); //adds pivot element to each topic
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
					$this->descendants($child, $levels - 1, $root)
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
					$this->ancestors($parent, $levels - 1, $root)
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
	 * @param  Topic|int  $topic the topic whose ancestors and descendants we'd like to print
	 */
	public static function asciiTree($topic)
	{
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