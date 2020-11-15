<?php

use Illuminate\Database\Seeder;

class TopicsTableSeeder extends Seeder
{
	/**
	 * The number of fake classes to make
	 */
	const NUM_TOPICS = 10;

    /**
     * Run the database seeds.
     *
     * @return void
     */
	public function run($run_with_fake_names = false)
	{
		if ($run_with_fake_names)
		{
			$this->fakedNames();
		}
		else
		{
			$this->numberedNames();
		}
	}

	/**
	 * Run the database seeds with names being numbered as "Item ".$id
	 */
	public function numberedNames()
	{
		$latest_id = 0; // default is 0
		// get the latest id in the table
		if (App\Topic::count() > 0)
		{
			$latest_id = App\Topic::orderBy('id', 'desc')->first()->id;
		}
		// create each class with the id
		for ($curr_id = ++$latest_id; $curr_id < self::NUM_TOPICS + $latest_id; $curr_id++)
		{
			factory('App\Topic')->create(['name' => 'Topic '.$curr_id]);
		}
	}

	/**
	 * Run the database seeds with faked names
	 */
	public function fakedNames()
	{
		factory('App\Topic', self::NUM_TOPICS)->create();
	}
}
