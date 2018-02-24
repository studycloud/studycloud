<?php

use Illuminate\Database\Seeder;

class PermissionsTableSeeder extends Seeder
{
	// what are Study Cloud's permissions?
	private $permissions = [];
	//note that implicit in this array are the permission_id's (defined by the order of each role in the array)

    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        foreach($this->permissions as $permission)
        {
        	App\Permission::firstOrCreate(['name' => $permission]);
        }
    }
}
