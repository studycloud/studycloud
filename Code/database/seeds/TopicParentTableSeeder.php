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
	 * Run the database seeds.
	 *
	 * @return void
	 */
	public function run($parent_topic = null, $curr_topics = null)
	{
		$min_num_topics_level = 0;
		if (!$parent_topic)
		{
			$curr_topics = App\Topic::pluck('id')->all();
			$min_num_topics_level = 1; // let's ensure that there's at least one top level topic
		}
		if (count($curr_topics)>0)
		{
			// how many topics should be at this level of the "tree"?
			$num_topics_level = rand($min_num_topics_level, count($curr_topics));
			$curr_topic_ids = $this->generateNewParents($parent_topic, $curr_topics, $num_topics_level);
			$new_curr_topics = array_values(array_diff($curr_topics, $curr_topic_ids));
			foreach ($curr_topic_ids as $new_parent_topic) {
				if (!in_array($new_parent_topic, $this->has_been_a_parent))
				{
					$this->run($new_parent_topic, $new_curr_topics);
					$this->has_been_a_parent[] = $new_parent_topic;
				}
			}
		}
	}

	/**
	 * Make new parental topics.
	 *
	 * @return array $curr_topic_ids
	 */
	private function generateNewParents($parent_topic, $curr_topics, $num_topics_level)
	{
		$this->refreshFaker();
		$curr_topic_ids = [];
		for ($i=0; $i<$num_topics_level; $i++)
		{
			$curr_topic_ids[] = $this->generateChildID($curr_topics, $num_topics_level);
		}
		$this->saveChildren($parent_topic, $curr_topic_ids);
		return $curr_topic_ids;
	}

	private function generateChildID($curr_topics, $num_topics_level)
	{
		return $this->faker->unique()->randomElement($curr_topics);
	}

	private function refreshFaker()
	{
		$this->faker = new Faker\Generator;
		$this->faker->addProvider(new Faker\Provider\Base($this->faker));
	}

	private function saveChildren($parent_topic, $curr_topic_ids)
	{
		if ($parent_topic)
		{
			App\Topic::find($parent_topic)->children()->attach($curr_topic_ids);
		}
	}
}
