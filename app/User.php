<?php

namespace App;

use Illuminate\Support\Facades\DB;
use Illuminate\Notifications\Notifiable;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Silber\Bouncer\Database\HasRolesAndAbilities;
use Bouncer;

class User extends Authenticatable
{
	use HasRolesAndAbilities;
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
	 * return the full name of this user
	 * @return string	the user's full name
	 */
	public function name()
	{
		return $this->fname . " " . $this->lname;
	}

	/**
	 * returns true if the user is an admin
	 */
	public function isAdmin() 
	{
		return !($this->getRoles()->isEmpty());
	}

	/**
	 * return a collection of the names of the different roles the user is assigned
	 */
	public function getRoles()
	{
		return $this->roles->pluck('name');
	}

	/**
	 * return the oauth meta data for this user
	 */
	public function oauth()
	{
		return $this->hasMany(UserOauth::class);
	}

	/**
	 * gives this user a role. returns false if the user already has it.
	 * addRole can take as input either a string representing a role (ex: "moderator") or an instance of Role. An exception will be raised if these criteria are not met
	 */
	public function addRole($role)
	{

		$this->assign('role');
		// $role = User::roleAsStringWrapper($role);
		// if ($this->hasRole($role))
		// {
		// 	return false;   
		// }
		// else
		// {
		// 	return $this->roles()->attach($role->id);
		// }
		
	}

	/**
	 * removes one of the user's roles. returns false if the user didn't have it to begin with
	 * can take as input either a string representing a role (ex: "moderator") or an instance of Role. An exception will be raised if these criteria are not met
	 */
	public function deleteRole($role)
	// an idea! --> allow deleteRole to accept an array of Roles
	{
		Bouncer::retract($role)->from($this);
		// $role = User::roleAsStringWrapper($role);
		// if ($this->hasRole($role))
		// {
		// 	return $this->roles()->detach($role->id);
		// }
		// else
		// {
		// 	return false;
		// }
	}

	/**
	 * returns true if this user has the specified role and false otherwise
	 * can take as input either a string representing a role (ex: "moderator") or an instance of Role. An exception will be raised if these criteria are not met
	 */
	public function hasRole($role)
	{
		Bouncer::is($user)->a($role);
		//$role = User::roleAsStringWrapper($role);
		//return $this->roles()->get()->contains($role);
	}

	// again, probs not relevant anymore
	/**
	 * returns all admins as a collection of User objects
	 */
	public static function getAllAdmins()
	{
		return self::getRoles()->pluck('name')->map(function($role, $key)
		{
			return User::whereIs($role)->get();
		});
		//User::whereIs('superadmin')->get();
		//return DB::table('roles')->get();
	}

	public static function getAllRoles()
	{
		return Bouncer::role()->all();
	}

	/**
	 * Retrieves the acceptable enum fields for a user type
	 * @return array	the available resource types
	 */
	public static function getPossibleTypes() {
		// Pulls column string from DB
		// we use (new static) to get an instance of the current class
		$enumStr = DB::select(DB::raw('SHOW COLUMNS FROM '.(new static)->getTable().' WHERE Field = "type"'))[0]->Type;
		// Parse enum string
		// should look something like:
		// 		enum('text','link','file')
		preg_match_all("/'([^']+)'/", $enumStr, $matches);
		// Return matches
		return isset($matches[1]) ? $matches[1] : [];
	}
}