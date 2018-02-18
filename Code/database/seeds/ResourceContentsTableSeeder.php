<?php

use Illuminate\Database\Seeder;

class ResourceContentsTableSeeder extends Seeder
{
    /**
     * the maximum number of contents each resource could plausibly have
     */
    const MAX_CONTENTS_PER_RESOURCE = 1; // for now, we will fix this number at 1 until the design team feels comfortable with multiple

    /**
     * this is here so that the closure inside the each function can work properly
     */
    protected $run_with_fake_names;

    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run($run_with_fake_names = false)
    {
        $this->run_with_fake_names = $run_with_fake_names;
    	// big picture: iterate through each resource and create a random number of content for them using the ResourceContent factory
        App\Resource::all()->each(
        	function($resource)
        	{
                // this resource might already have content. let's only add as much as we're allowed to
                $allowed_num_contents = self::MAX_CONTENTS_PER_RESOURCE - $resource->contents()->count();
                // how many contents do we want the current resource to have?
        		$num_contents = rand(1, self::MAX_CONTENTS_PER_RESOURCE);
                if ($num_contents > $allowed_num_contents)
                {
                    $num_contents = $allowed_num_contents;
                }
                if ($this->run_with_fake_names)
                {
                    $this->fakedNames($num_contents, $resource->id);
                }
                else
                {
                    $this->numberedNames($num_contents, $resource->id);
                }
        	}
        );
    }

    /**
     * Run the database seeds with names being numbered as "Item ".$id
     */
    public function numberedNames($num_contents, $resource_id)
    {
        $latest_id = 0; // default is 0
        // get the latest id in the table
        if (App\ResourceContent::count() > 0)
        {
            $latest_id = App\ResourceContent::orderBy('id', 'desc')->first()->id;
        }
        // create each class with the id
        for ($curr_id = ++$latest_id; $curr_id < $num_contents + $latest_id; $curr_id++)
        {
            // let's use a factory to generate contents for each resource
            // note that we must inject the resource_id via an argument to the create() method
            factory('App\ResourceContent')->create(['name' => 'Resource Content '.$curr_id, 'resource_id' => $resource_id]);
        }
    }

    /**
     * Run the database seeds with faked names
     */
    public function fakedNames($num_contents, $resource_id)
    {
        // let's use a factory to generate contents for each resource
        // note that we must inject the resource_id via an argument to the create() method
        factory('App\ResourceContent', $num_contents)->create(['resource_id' => $resource_id]);
    }
}
