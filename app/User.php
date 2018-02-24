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
		'fname', 'lname', 'email', 'password',
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
	 * returns the roles of this user. make sure to call get on the result!
	 */
	public function roles()
	{
		return $this->belongsToMany(Role::class, 'role_user', 'user_id', 'role_id');
	}

	/**
	 * gives this user a role. returns false if the user already has it.
	 * addRole can take as input either a string representing a role (ex: "moderator") or an instance of Role. An exception will be raised if these criteria are not met
	 */
	public function addRole($role)
	{
		$role = User::roleAsStringWrapper($role);
		if ($this->hasRole($role))
		{
			return false;   
		}
		else
		{
			return $this->roles()->attach($role->id);
		}
		
	}

	/**
	 * removes one of the user's roles. returns false if the user didn't have it to begin with
	 * can take as input either a string representing a role (ex: "moderator") or an instance of Role. An exception will be raised if these criteria are not met
	 */
	public function deleteRole($role)
	// an idea! --> allow deleteRole to accept an array of Roles
	{
		$role = User::roleAsStringWrapper($role);
		if ($this->hasRole($role))
		{
			return $this->roles()->detach($role->id);
		}
		else
		{
			return false;
		}
	}

    // I'm not sure if this is still relevant
    // /**
    //  * returns true if this user is an administrator and false otherwise
    //  */
    // public function isAdmin()
    // {
    //     return !($this->roles()->get()->isEmpty());
    // }

	/**
	 * returns true if this user has the specified role and false otherwise
	 * can take as input either a string representing a role (ex: "moderator") or an instance of Role. An exception will be raised if these criteria are not met
	 */
	public function hasRole($role)
	{
		$role = User::roleAsStringWrapper($role);
		return $this->roles()->get()->contains($role);
	}

	// again, probs not relevant anymore
	// /**
	//  * returns all users as a collection of User objects
	//  */
	// public static function getAllAdmins()
	// {
	//     return AdminUserRole::getAllAdmins();
	// }

	/**
	 * wrapper function to map strings representing roles to their Role instance counterparts
	 */
	private static function roleAsStringWrapper($role){
		if (is_string($role))
		{
			return Role::getRole($role);
		}
		elseif (is_a($role, get_class(new Role)))
		{
			return $role;
		}
		throw new \InvalidArgumentException("this function only accepts either a string representing a role or an instance of Role");
	}
}