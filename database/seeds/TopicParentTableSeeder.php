<?php

use Illuminate\Database\Seeder;

class TopicParentTableSeeder extends Seeder
{
    /**
     * The faker generator associated with this seeder
     *
     * @var Faker\Generator
     */
	protected $faker;

    /**
     * The list of topics that have become parents (i.e. have been assigned children).
     *
     * @var array
     */
	protected $has_been_a_parent = [];

	/**
	 * What is the maximum number of topics that each level of the tree can have?
	 */
	const NUM_MAX_TOPICS = 5;

	/**
	 * Run the database seeds.
	 *
	 * @return void
	 */
	public function run($parent_topic = null, $curr_topics = null)
	{
		// set the minimum number of topics for this level to be 0 for now; we might change it later
		$num_topics_level_min = 0;
		// parent_topic and curr_topics will be null if this is the first time the run function is called
		if (!$parent_topic)
		{
			// set the ids of all of the current topics
			$curr_topics = App\Topic::pluck('id')->all();
			// let's ensure that there's at least one top level topic
			$num_topics_level_min = 1;
		}
		if (count($curr_topics)>0)
		{
			// how many topics should be at this level of the "tree"?
			$num_topics_level = rand($num_topics_level_min, self::NUM_MAX_TOPICS);
			// delegate the task of assigning children and get the ids of the chosen children
			$curr_topic_ids = $this->assignChildren($parent_topic, $curr_topics, $num_topics_level);
			// get the leftover topics that haven't been chosen as children yet
			$new_curr_topics = array_values(array_diff($curr_topics, $curr_topic_ids));
			// recur with each chosen child as a new parent!
			foreach ($curr_topic_ids as $new_parent_topic) {
				// but don't try to find children for a topic if it already has some
				if (!in_array($new_parent_topic, $this->has_been_a_parent))
				{
					// recursion!
					$this->run($new_parent_topic, $new_curr_topics);
					$this->has_been_a_parent[] = $new_parent_topic;
				}
			}
		}
	}

	/**
	 * assign children to a parent topic
	 * @param  int $parent_topic     	the id of the parent topic
	 * @param  array $curr_topics      	a list of all the topic ids that could be children of this parent
	 * @param  int $num_topics_level	the number of topics to assign as children
	 * @return array                   	the topics that were assigned as children (and will become new parents!)
	 */
	private function assignChildren($parent_topic, $curr_topics, $num_topics_level)
	{
		// recreate the faker instance so that unique() works correctly
		$this->refreshFaker();
		// an array that will hold the ids of the topics that we pick to be children of $parent_topic
		$curr_topic_ids = [];
		for ($i=0; $i<$num_topics_level; $i++)
		{
			$curr_topic_ids[] = $this->pickChildID($curr_topics);
		}
		$this->saveChildren($parent_topic, $curr_topic_ids);
		return $curr_topic_ids;
	}

	/**
	 * reset the faker generator instance
	 * @return null
	 */
	private function refreshFaker()
	{
		// create a new faker instance
		$this->faker = new Faker\Generator;
		// add the base provider so that we can use the unique() function later
		$this->faker->addProvider(new Faker\Provider\Base($this->faker));
	}

	/**
	 * randomly pick a child (which hasn't been picked before) from the list of children
	 * @param  array $curr_topics 	a list of topic ids from which to pick the child
	 * @return int 					the id of the chosen topic
	 */
	private function pickChildID($curr_topics)
	{
		return $this->faker->unique()->randomElement($curr_topics);
	}

	/**
	 * persist a list of children to the database
	 * @param  int $parent_topic   		the id of the parent topic
	 * @param  array $curr_topic_ids 	a list of children ids for this parent
	 * @return null
	 */
	private function saveChildren($parent_topic, $curr_topic_ids)
	{
		if ($parent_topic)
		{
			App\Topic::find($parent_topic)->children()->attach($curr_topic_ids);
		}
	}
}
