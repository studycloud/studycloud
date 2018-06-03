<?php

use Illuminate\Database\Seeder;

use App\Resource;
use App\Topic;
use App\Repositories\ResourceRepository;

class ResourceTopicTableSeeder extends Seeder
{
	/**
	 * What is the maximum number of topics that each resource can have?
	 */
	const NUM_MAX_TOPICS = 6;

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
		
		Resource::all()->each(
			function($curr_resource) use ($num_total_topics)
			{
				// how many topics do we want the current resource to have?
				$curr_num_topics = rand(0, min(self::NUM_MAX_TOPICS, $num_total_topics));
				// delegate responsibility for adding the topics
				$this->assignTopics($curr_resource, $curr_num_topics);
			}
		);
	}

	/**
	 * validly assign topics to $resource
	 * @param  Resource $resource   the resource to assign topics to
	 * @param  int   	$num_topics the number of topics to assign
	 */
	private function assignTopics(Resource $resource, int $num_topics)
	{
		for ($i=0; $i<$num_topics; $i++)
		{
			// what topic can this resource be assigned to?
			$available_topics = ResourceRepository::allowedTopics($resource);
			// check that there are enough topics first!
			if ($available_topics->count() > 0)
			{
				// pick a topic randomly, then add it to the resource
				$topic = $available_topics->random();
				$resource->topics()->attach($topic);
			}
		}
	}
}
