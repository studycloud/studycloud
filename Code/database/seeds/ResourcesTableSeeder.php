<?php

use Illuminate\Database\Seeder;

class ResourcesTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        factory(App\Resource::class, 40)->create()->each(function ($resource)
        {
        	$num_contents = rand(1, 5);
        	$resource->contents()->saveMany(
        		factory(App\ResourceContent::class, $num_contents)->make()
        	);
        });
    }
}