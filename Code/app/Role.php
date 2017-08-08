<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Role extends Model
{
	/**
	 * Indicates if the model should be timestamped.
	 *
	 * @var bool
	 */
	public $timestamps = false;

	/**
	 * returns all users with this role
	 */
	public function getUsers(){
		return $this->belongsToMany(User::class, 'role_user', 'role_id',  'user_id');
	}

	/**
	 * given a string representating a role, returns the corresponding instance as a role or null if it does not exist
	 */
	public static function getRole($role)
	{
		// get the role where the 'name' column from the database matches the specified role name
		// ucwords() is used so that the function can accept uncapatilized role names
		return Role::where('name',ucwords($role))->first();
	}
}
