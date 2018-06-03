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
     * The faker generator associated with this seeder
     *
     * @var Faker\Generator
     */
	protected $faker;

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
		// recreate the faker instance so that unique() works correctly
		$this->refreshFaker();
		for ($i=0; $i<$num_topics; $i++)
		{
			// what topics can this resource be assigned to?
			$available_topics = ResourceRepository::allowedTopics($resource)->pluck('id')->all();
			// pick a topic randomly, then add it to the resource
			$resource->topics()->attach($this->pickTopicID($available_topics));
		}
	}

	/**
	 * reset the faker generator instance
	 */
	private function refreshFaker()
	{
		// create a new faker instance
		$this->faker = new Faker\Generator;
		// add the base provider so that we can use the unique() function later
		$this->faker->addProvider(new Faker\Provider\Base($this->faker));
	}

	/**
	 * randomly pick a topic (which hasn't been picked before) from the list
	 * of available topics
	 * @param  array $topics 	a list of topic ids from which to pick the child
	 * @return int 				the id of the chosen topic
	 */
	private function pickTopicID($topics)
	{
		return $this->faker->unique()->randomElement($topics);
	}
}
