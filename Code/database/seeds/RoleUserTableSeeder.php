<?php

use Illuminate\Database\Seeder;

class RoleUserTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
    	// big picture: iterate through each user and create a random number of roles for them using the RoleUser factory
        App\User::all()->each(
        	function($curr_user)
        	{
                // how many roles are in the roles table?
                $num_total_roles = App\Role::all()->count();
                // how many roles do we want the current user to have?
        		$curr_num_roles = rand(0, $num_total_roles);
                // let's use a factory to generate roles for each user
                // note that we must inject the user_id via an argument to the create() method
                factory('App\RoleUser', $curr_num_roles)
                    ->create( ['user_id' => $curr_user->id] );
        	}
        );
    }
}
