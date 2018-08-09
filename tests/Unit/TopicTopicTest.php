<?php

namespace Tests\Unit;

use App\Topic;
use Tests\TestCase;
use App\Repositories\TopicRepository;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;

/**
 * A test class for verifying that all topic-to-topic connections in the database are legitimate.
 */
class TopicTopicTest extends TestCase
{
	/**
	 * A basic test example.
	 *
	 * @return void
	 */
	public function testValidTopics()
	{
		print_r(Topic::all()->map(
			function ($topic)
			{
				return $this->trySubTree($topic);
			}
		)->toArray());
	}

	/**
	 * Looks for conflicting topics in the descendants of the subtree with
	 * $root as the root
	 * A topic is found to be conflicting if the descendants of $root
	 * (excluding direct children) contain any of the direct children of $root
	 * @param  Topic|null	$root	the topic at the root of this subtree
	 * @return array				any conflicting topics found in this subtree as keys and the frequency of conflict as values
	 */
	private function trySubTree(Topic $root = null)
	{
		// get immediate children of $root
		if (is_null($root))
		{
			$children = TopicRepository::getTopLevelTopics();
		}
		else
		{
			$children = $root->children;
		}
		// get all descendants of each child
		$descendants = collect();
		foreach ($children as $child) {
			$descendants = $descendants->merge((new TopicRepository)->descendants());
		}
		// get conflicting topics
		$conflicts = $descendants->pluck('id')->intersect($children->pluck('id'))->toArray();
		return array_count_values($conflicts);
	}
}
