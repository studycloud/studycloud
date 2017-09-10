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
	 * The attributes that are mass assignable.
	 *
	 * @var array
	 */
	protected $fillable = ['name'];

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
		if (!is_string($role))
		{
			throw new Exception("Argument $role must be a string.");
			
		}
		// get the role where the 'name' column from the database matches the specified role name
		// ucwords() is used so that the function can accept uncapatilized role names
		return Role::where('name',ucwords($role))->first();
	}

    /**
     * wrapper function to map strings representing roles to their Role instance counterparts
     */
    private static function roleAsString($role){
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
