<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class AdminJob extends Model
{
    /**
     * returns all admins
     */
    public function getAdmins(){
    	return $this->belongsToMany(User::class, 'admin_user_jobs', 'jobid',  'userid');
    }

    /**
     * 
     */
    public static function 
}