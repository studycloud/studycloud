<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Permission extends Model
{
	/**
	 * Indicates if the model should be timestamped.
	 *
	 * @var bool
	 */
	public $timestamps = false;

	/**
	 * given a string representating a permission, returns the corresponding instance as a permission or null if it does not exist
	 */
	public static function getPermission($permission)
	{
		if (!is_string($role))
		{
			throw new Exception("Argument $permission must be a string.");
			
		}
		// get the permission where the 'name' column from the database matches the specified permission name
		// ucwords() is used so that the function can accept uncapatilized permission names
		return Permission::where('name',ucwords($permission))->first();
	}
}
