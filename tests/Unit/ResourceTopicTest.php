<?php

namespace Tests\Unit;

use Tests\TestCase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;

use Illuminate\Database\Eloquent\Collection;
use App\Topic;
use App\Repositories\TopicRepository;
use App\Resource;

class ResourceTopicTest extends TestCase
{
	/**
	 * Test whether the resources in the database are attached
	 * to topics in a valid way.
	 *
	 * @return void
	 */
	public function testValidResources()
	{
		// strategy: walk down the topic tree and check whether resources
		// appear more than once in child topics
		$invalid_resources = $this->invalidResources(null, collect());
		$error_message = "The invalid resources were: " . implode(", ", $invalid_resources->sort()->all()) . ".";
		$this->assertEmpty($invalid_resources, $error_message);
	}

	/**
	 * Returns a collection of resources that are attached to topics in
	 * an invalid way. This function will recurse down the Topic tree in
	 * search of resources that appear more than once in the children of
	 * a given Topic in the tree.
	 * @param  Topic|null 	$topic     	the root of the subtree on which to recurse (or null if the Topic Tree's root)
	 * @param  Collection	$resources 	a collection of resource IDs that have already occurred in $topic or its ancestors
	 * @return Collection				a collection of resource IDs that have appeared more than once
	 */
	private function invalidResources(Topic $topic = null, $resources)
	{
		// get the children of $topic (or the topLevelTopics if $topic is null)
		if ($topic === null)
		{
			$children = TopicRepository::getTopLevelTopics();
		}
		else
		{
			$children = $topic->children;
		}
		// iterate through each child of $topic and call invalidResources on
		// them, merging the results with a collection of resources that were
		// both in $resources and the child's resources
		return $children->map(
			function ($child) use ($resources)
			{
				// get this child's resources as a collection of IDs
				$child_resources = $child->resources->pluck('id');
				// create a collection of resources that are both in $resources
				// and in $child_resources using intersect(), then merge the
				// result with this child's invalid resources
				return $resources->intersect($child_resources)->merge(
					// recursion! remember to merge $resources and 
					// $child_resources, since we want to build up a growing
					// collection of resources that have occurred on our way
					// down the tree
					$this->invalidResources(
						$child,
						$resources->merge($child_resources)
					)
				)->all(); // use all() to convert to a standard array
			}
		)->collapse()->unique(); // use collapse() to flatten the arrays
	}
}
