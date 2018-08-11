<?php

namespace Tests\Unit;

use App\Topic;
use Tests\TestCase;
use App\Helpers\NestedArrays;
use App\Repositories\TopicRepository;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;

/**
 * A test class for verifying that all topic-to-topic connections in the database are legitimate.
 */
class TopicTopicTest extends TestCase
{
	/**
	 * Test whether the topics in the database are attached
	 * to each other in a valid way.
	 *
	 * @return void
	 */
	public function testValidTopics()
	{
		$conflicts = $this->conflictingTopics();
		$conflicts_exist = false;
		$conflict_list = [];
		$message = "Conflicting topics were found in the following subtrees: \n";
		foreach ($conflicts as $topic_id => $conflicting) {
			if (!empty($conflicting))
			{
				$conflicts_exist = true;
				$conflict_list = array_merge($conflict_list, array_keys($conflicting));
				$message .= $topic_id == 0 ? "root" : $topic_id;
				$message .= " has " . implode(", ", array_keys($conflicting)) . "\n";
				if ($topic_id != 0)
				{					
					$message .= NestedArrays::convertToAscii(NestedArrays::topicDescendants(Topic::find($topic_id)));
				}
			}
		}
		sort($conflict_list);
		$message .= "Summary: " . implode(", ", array_unique($conflict_list)) . " were found to be in conflict.";
		$this->assertFalse($conflicts_exist, $message);
	}

	/**
	 * Looks for conflicting topics in all subtrees of the topic tree.
	 * @return array the conflicting topics of each subtree with the subtree id as keys
	 */
	public function conflictingTopics()
	{
		$conflicts = collect([0 => $this->trySubTree()]);
		$conflicts = $conflicts->merge(
			Topic::all()->keyBy('id')->map(
				function ($topic)
				{
					return $this->trySubTree($topic);
				}
			)
		);
		return $conflicts->toArray();
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
			$descendants = $descendants->merge((new TopicRepository)->descendants($child));
		}
		// get conflicting topics
		$conflicts = $descendants->pluck('id')->intersect($children->pluck('id'))->toArray();
		return array_count_values($conflicts);
	}
}
