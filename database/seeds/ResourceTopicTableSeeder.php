<?php

use App\Topic;
use App\Resource;
use App\TopicParent;
use Illuminate\Database\Seeder;
use Illuminate\Support\Collection;
use App\Repositories\TopicRepository;
use App\Repositories\ResourceRepository;

class ResourceTopicTableSeeder extends Seeder
{
	/**
	 * What is the maximum number of topics that each resource can have?
	 */
	const NUM_MAX_TOPICS = 6;

	/**
	 * With what probability should resources be assigned to topics farther
	 * down the tree than those closer to the root?
	 * Use 1 if you want resource-topic attachments to be weighted by topic
	 * depth or 0 if you want resources to be assigned to topics completely
	 * randomly. A weight much greater than 1 will force resources to be at the
	 * leafs and a negative weight will put resources closer to the root, instead.
	 * For more info, see the docstring for the "scale" parameter of the wrand() function in app\Helpers\Helper.php
	 */
	const WEIGHT = 2;

	/**
	 * If WEIGHT is non-zero, how should the depth of each topic be calculated?
	 * You can use...
	 * - "average" if it should be the mean of all depths for that topic,
	 * - "median" if it should be the middle depth for that topic,
	 * - "max" if it should be the longest depth,
	 * - "min" if it should be the shortest depth, and
	 * - "mode" if it should be the most common depth
	 */
	const DEPTH_METHOD = "average";

	/**
	 * Run the database seeds.
	 *
	 * @return void
	 */
	public function run()
	{
		// big picture: iterate through each resource and create a random number of topics for them using the ResourceTopic factory
		// since we want every resource to have at least one topic

		// how many topics are in the topics table?
		$num_total_topics = Topic::count(); 
		
		// get the depths of each topic for later use
		// apply the DEPTH_METHOD to each array of depths
		$depths = TopicRepository::depths(TopicParent::all(), 1)->map->{self::DEPTH_METHOD}();

		Resource::all()->each(
			function($curr_resource) use ($num_total_topics, $depths)
			{
				// how many topics do we want the current resource to have?
				$curr_num_topics = rand(0, min(self::NUM_MAX_TOPICS, $num_total_topics));
				// delegate responsibility for adding the topics
				$this->assignTopics($curr_resource, $curr_num_topics, $depths);
			}
		);
	}

	/**
	 * validly assign topics to $resource
	 * @param  Resource 	$resource   the resource to assign topics to
	 * @param  int   		$num_topics the number of topics to assign
	 * @param  Collection	$depths		the depths of all relevant topics as (topic_id, depth) key-value pairs
	 */
	private function assignTopics(Resource $resource, int $num_topics, Collection $depths)
	{
		for ($i=0; $i<$num_topics; $i++)
		{
			// what topic can this resource be assigned to?
			$available_topics = ResourceRepository::allowedTopics($resource);
			// check that there are enough topics first!
			if ($available_topics->count() > 0)
			{
				// get a subset of the depths according to the topics that are available
				$depths_subset = $depths->only($available_topics->pluck('id'));
				if ($depths_subset->count() > 0)
				{
					// pick a topic randomly using wrand(), where the weights are calculated from the topic's depths
					$topic = wrand($depths_subset, self::WEIGHT);
					// then add that topic to the resource
					$resource->topics()->attach($topic);
				}
			}
		}
	}
}
