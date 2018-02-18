<?php

use Illuminate\Database\Seeder;

class ResourceTopicTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
    	// big picture: iterate through each resource and create a random number of topics for them using the ResourceTopic factory
        App\Resource::all()->each(
        	function($curr_resource)
        	{
                // how many topics are in the topics table?
                $num_total_topics = App\Topic::all()->count();
                // how many topics do we want the current resource to have?
        		$curr_num_topics = rand(0, $num_total_topics);
                // let's use a factory to generate topics for each resource
                // note that we must inject the resource_id via an argument to the create() method
                factory('App\ResourceTopic', $curr_num_topics)
                    ->create( ['resource_id' => $curr_resource->id] );
        	}
        );
    }
}
