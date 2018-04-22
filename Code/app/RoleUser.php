<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class RoleUser extends Model
{
	/**
	 * The table associated with the model.
	 *
	 * @var string
	 */
	protected $table = 'role_user';

	/**
	 * Indicates if the model should be timestamped.
	 *
	 * @var bool
	 */
	public $timestamps = false;

	/**
	 * The attributes that are mass assignable.
	 *
	 * @var array
	 */
	protected $fillable = ['user_id', 'role_id'];

	// this code was copy-pasted from the AdminUserJob Model and must be applied to the new RoleUser Model, but it might not be relevant anymore
	// /**
	//  * returns all admins as a collection of User objects
	//  */
	// public static function getAllAdmins()
	// {
	// 	return AdminUserJob::select('userid')->distinct()->get()
	// 		->transform(function ($adminUserJob)
	   //  	{
	   //  		return User::find($adminUserJob['userid']);
	   //  	});
	// }
}
