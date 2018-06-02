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
	 * @return Collection 				a collection of resource IDs that have appeared more than once
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

		return $children->map(
			function ($child) use ($resources)
			{
				$child_resources = $child->resources->pluck('id');
				return $resources->intersect($child_resources)->merge(
					$this->invalidResources(
						$child,
						$resources->merge($child_resources)
					)
				)->all();
			}
		)->collapse()->unique();
	}
}
