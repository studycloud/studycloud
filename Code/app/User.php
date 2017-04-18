<?php

namespace App;

use Illuminate\Notifications\Notifiable;
use Illuminate\Foundation\Auth\User as Authenticatable;

class User extends Authenticatable
{
    use Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name', 'email', 'password',
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password', 'remember_token',
    ];

    /**
     * returns the jobs of this user. make sure to call get on the result!
     */
    public function adminJobs()
    {
        return $this->belongsToMany(AdminJob::class, 'admin_user_jobs', 'userid', 'jobid');
    }

    /**
     * gives this user an admin job. returns false if the user already has it
     */
    public function addJob(AdminJob $job)
    {
        if ($this->hasJob($job))
        {
            return false;   
        }
        else
        {
            return $this->adminJobs()->save($job);
        }
    }

    /**
     * removes one of the user's jobs. returns false if the user didn't have it to begin with
     */
    public function deleteJob(AdminJob $job)
    {
        if ($this->hasJob($job))
        {
            return $this->adminJobs()->wherePivot('jobid',$job->id)->first()->pivot->delete();
        }
        else
        {
            return false;
        }
    }

    /**
     * returns true if this user is an administrator and false otherwise
     */
    public function isAdmin()
    {
        return !($this->adminJobs()->get()->isEmpty());
    }

    /**
     * returns true if this user has the specified job and false otherwise
     */
    public function hasJob(AdminJob $job)
    {
        return $this->adminJobs()->get()->contains($job);
    }

    /**
     * returns true if this user is a dictator and false otherwise
     */
    public function isDictator()
    {
        return hasJob(App\AdminJob::find(1));
    }

    // public function 
}