<?php

use Illuminate\Database\Seeder;

class ResourceTypesTableSeeder extends Seeder
{
	/**
	 * A list of the names of each resource type
	 * Note that implicit in this list are the ids
	 * (defined by the order of each resource_type in the array)
	 * @var array
	 */
	private $resource_types = ['link', 'video'];

    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        foreach($this->resource_types as $type)
        {
        	App\ResourceType::firstOrCreate(['name' => $type]);
        }
    }
}
