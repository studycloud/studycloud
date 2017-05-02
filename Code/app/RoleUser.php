<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class RoleUser extends Model
{
    /**
     * Indicates if the model should be timestamped.
     *
     * @var bool
     */
    public $timestamps = false;

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
