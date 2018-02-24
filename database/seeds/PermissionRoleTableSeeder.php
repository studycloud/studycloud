<?php

use Illuminate\Database\Seeder;

class PermissionRoleTableSeeder extends Seeder
{
	// which permissions does each role have?
	// let's map roles to an array of permissions
	// you can use either the name (as a str) or the id (as an int) of a role or permission in order to refer to it below. I recommend using the id in case the name is changed slightly in the future. however, names may be easier to read
	private $permission_roles = [
		'Dictator' => [],
		'Moderator' => [],
		'Organizer' => [],
		'Account Manager' => [],
		'Promoter' => []
	];


    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        foreach($this->permission_roles as $role => $permissions)
        {
        	if (is_int($role))
        	{
        		$role_id = $role;
        	}
        	elseif (is_string($role)) // attempt to convert to a role_id
        	{
        		$role_id = App\Role::getRole($role)->id;
        	}
        	
	        foreach ($permissions as $permission) {
	        	if (is_int($permission))
	        	{
	        		$permission_id = $permission;
	        	}
	        	elseif (is_string($permission)) // attempt to convert to a permission_id
	        	{
	        		$permission_id = App\Permission::getPermission($permission)->id;
	        	}
        		App\PermissionRole::firstOrCreate(['permission_id' => $permission_id, 'role_id' => $role_id]);
        	}
        }
    }
}
