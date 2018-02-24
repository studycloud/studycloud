<?php

use Illuminate\Database\Seeder;

class ResourceUsesTableSeeder extends Seeder
{
	/**
	 * A list of the names of each resource use
	 * Note that implicit in this list are the ids
	 * (defined by the order of each resource_use in the array)
	 * @var array
	 */
	private $resource_uses = ['Class Notes', 'Notes', 'Flashcards'];

    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        foreach($this->resource_uses as $use)
        {
        	App\ResourceUse::firstOrCreate(['name' => $use]);
        }
    }
}
