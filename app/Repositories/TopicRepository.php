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
	public function descendants(Topic $topic = null, int $levels = null)
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
					$this->descendants($child, $levels - 1)
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
	public function ancestors(Topic $topic = null, int $levels = null)
	{
		$tree = collect();
		// base case: $levels == 0
		// also do a memoization check to prevent us from
		// executing a query for a topic that we've already found
		if (($levels != 0 || is_null($levels)) && !in_array($topic->id, $this->memoize))
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
			}
			foreach ($parents as $parent) {
				// add the parent to the tree
				$tree->push(collect($parent));
				$tree = $tree->merge(
					// RECURSION!
					$this->ancestors($parent, $levels - 1)
				);
			}
		}
		return $tree;
	}

	/**
	 * print the descendants of $topic as an ascii tree
	 * @param  Topic  $topic the topic whose descendants we'd like to print
	 */
	public static function printAsciiDescendants(Topic $topic)
	{
		echo NestedArrays::convertToAscii(NestedArrays::topicDescendants($topic));
	}

	/**
	 * print the ancestors of $topic as an ascii tree
	 * @param  Topic  $topic the topic whose ancestors we'd like to print
	 */
	public static function printAsciiAncestors(Topic $topic)
	{
		echo NestedArrays::convertToAscii(NestedArrays::topicAncestors($topic));
	}

	/**
	 * convenience function for printing both ancestors and descendants of $topic as an ascii tree
	 * @param  Topic  $topic the topic whose ancestors and descendants we'd like to print
	 */
	public static function asciiTree(Topic $topic)
	{
		echo "DESCENDANTS\n";
		self::printAsciiDescendants($topic);
		echo "\nANCESTORS\n";
		self::printAsciiAncestors($topic);
	}
}