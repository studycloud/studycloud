<?php

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
    	if (!App::environment('production','staging'))
    	{
    		$this->call('UsersTableSeeder');
    	}
    	// call the seeders that populate roles and permissions and whatnot
    }
}
