<?php

use Illuminate\Database\Seeder;

class RolesTableSeeder extends Seeder
{
	// what are Study Cloud's roles?
	private $roles = ['Dictator', 'Moderator', 'Organizer', 'Account Manager', 'Promoter'];
	//note that implicit in this array are the role_id's (defined by the order of each role in the array)

    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        foreach($this->roles as $role)
        {
        	App\Role::firstOrCreate(['name' => $role]);
        }
    }
}
