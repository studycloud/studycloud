<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class AdminJob extends Model
{
    /**
     * returns all admins with this job
     */
    public function getAdmins(){
    	return $this->belongsToMany(User::class, 'admin_user_jobs', 'jobid',  'userid');
    }

    /**
     * given a string representating an admin job, returns the corresponding instance as an AdminJob or null if it does not exist
     */
    public static function getJob($job)
    {
        // get the AdminJob where the 'jobname' column from the database matches the specified jobname
        // ucwords() is used so that the function can accept uncapatilized jobnames
        return AdminJob::where('jobname',ucwords($job))->first();
    }
}