<?php

use Illuminate\Database\Seeder;

class ResourceTypesTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        factory('App\ResourceType', 10)->create();
    }
}
