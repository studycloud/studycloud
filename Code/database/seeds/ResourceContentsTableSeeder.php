<?php

use Illuminate\Database\Seeder;

class ResourceContentsTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
    	// big picture: iterate through each resource and create a random number of content for them using the ResourceContent factory
        App\Resource::all()->each(
        	function($resource)
        	{
                // how many topics do we want the current resource to have?
        		$num_contents = rand(1, 5);
                // let's use a factory to generate contents for each resource
                // note that we must inject the resource_id via an argument to the create() method
                factory('App\ResourceContent', $num_contents)
                    ->create( ['resource_id' => $resource->id] );
        	}
        );
    }
}
