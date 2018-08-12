<?php

use App\Topic;
use Illuminate\Database\Seeder;

class TopicParentTableSeeder extends Seeder
{
	/**
	 * What is the maximum number of topics that each level of the tree can have?
	 */
	const NUM_MAX_TOPICS = 4;

	/**
	 * An array of topics that have become parents (i.e. have been assigned children)
	 * as keys and an array of their descendants as values.
	 *
	 * @var array
	 */
	protected $parents = [];

	/**
	 * Run the database seeds.
	 *
	 * @return void
	 */
	public function run($parent = null, $curr_topics = null)
	{
		// set the minimum number of topics for this level to be 0 for now; we might change it later
		$num_topics_level_min = 0;
		// parent and curr_topics will be null if this is the first time the run function is called
		if (!$parent)
		{
			// get the ids of all of the current topics
			$curr_topics = Topic::pluck('id');
			// let's ensure that there's at least one top level topic
			$num_topics_level_min = 1;
		}
		if ($curr_topics->count()>0)
		{
			// how many topics should be at this level of the "tree"?
			$num_topics_level = rand( $num_topics_level_min, min(self::NUM_MAX_TOPICS, $curr_topics->count()) );
			// delegate the task of assigning children and get the ids of the chosen children
			$curr_topic_ids = $this->assignChildren($parent, $curr_topics, $num_topics_level);
			// get the leftover topics that haven't been chosen as children yet
			$new_curr_topics = $curr_topics->diff($curr_topic_ids);
			echo "-----\n";
			echo "parent is " . $parent . "\n";
			echo "curr_topics count is " . $curr_topics->count() . "\n";
			echo "curr_topics are " . implode(", ", $curr_topics->toArray()) . "\n";
			echo "chosen children are " . implode(", ", $curr_topic_ids) . "\n";
			echo "new_curr_topics count is " . $curr_topics->count() . "\n";
			echo "new curr_topics are " . implode(", ", $new_curr_topics->toArray()) . "\n";
			// set up the descendants array for returning it later
			$descendants = $curr_topic_ids;
			// recur with each chosen child as a new parent!
			foreach ($curr_topic_ids as $new_parent) {
				// but don't try to find children for a topic if it already has some
				if (!array_key_exists($new_parent, $this->parents))
				{
					// recursion!
					$this->parents[$new_parent] = $this->run($new_parent, $new_curr_topics);
				}
				// continue to keep track of descendants
				$descendants = array_merge($descendants, $this->parents[$new_parent]);
			}
			if (!$parent)
			{
				print_r($this->parents);
			}
			return $descendants;
		}
		return [];
	}

	/**
	 * assign children to a parent topic
	 * @param  int $parent     	the id of the parent topic
	 * @param  Collection $topics      	a list of all the topic ids that could be children of this parent
	 * @param  int $num_topics_level	the number of topics to assign as children
	 * @return array                   	the topics that were assigned as children (and will become new parents!)
	 */
	private function assignChildren($parent, $topics, $num_topics_level)
	{
		// use a copy of $topics rather than the copy of the reference to the object, so as not to accidentally change $curr_topics
		$topics = collect($topics);
		// an array that will hold the ids of the topics that we pick to be children of $parent
		$curr_topic_ids = [];
		while ($num_topics_level>0 && $topics->count()>0)
		{
			$choice = $this->pickChildID($topics, $curr_topic_ids);
			// check whether a child was chosen
			if ($choice)
			{
				$num_topics_level--;
				$curr_topic_ids[] = $choice;
				// does the child we chose already have children assigned to it?
				if (array_key_exists($choice, $this->parents))
				{
					// any of the child's descendants cannot be chosen as children of the current $parent
					$topics = $topics->diff($this->parents[$choice]);
				}
			}
		}
		$this->saveChildren($parent, $curr_topic_ids);
		return $curr_topic_ids;
	}

	/**
	 * randomly pick a child (which hasn't been picked before) from the list of children
	 * filter $topics (in place)
	 * @param  array $topics 	a list of topic ids from which to pick the child
	 * @return int 					the id of the chosen topic
	 */
	private function pickChildID(&$topics, $chosen_children)
	{
		$choice = null;
		while ($topics->count()>0)
		{
			$choice = $topics->random();
			// now that we've chosen this topic, let's remove it from those that are available
			$topics->forget($topics->search($choice, true));
			// does the child we chose already have children assigned to it?
			// if it does, check that its descendants don't conflict with any children we've already chosen
			if (
				!array_key_exists($choice, $this->parents) ||
				!array_intersect($this->parents[$choice], $chosen_children)
			)
			{
				break;
			}
			$choice = null;
		}
		return $choice;
	}

	/**
	 * persist a list of children to the database
	 * @param  int $parent   		the id of the parent topic
	 * @param  array $curr_topic_ids 	a list of children ids for this parent
	 */
	private function saveChildren($parent, $curr_topic_ids)
	{
		if ($parent)
		{
			Topic::find($parent)->children()->attach($curr_topic_ids);
		}
	}
}
