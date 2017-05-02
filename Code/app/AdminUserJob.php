<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class AdminUserJob extends Model
{
    /**
     * Indicates if the model should be timestamped.
     *
     * @var bool
     */
    public $timestamps = true;

    /**
     * returns all admins as a collection of User objects
     */
    public static function getAllAdmins()
    {
    	return AdminUserJob::select('userid')->distinct()->get()
    		->transform(function ($adminUserJob)
	    	{
	    		return User::find($adminUserJob['userid']);
	    	});
    }
}