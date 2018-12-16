<?php

use Illuminate\Database\Seeder;

class UsersTableSeeder extends Seeder
{
	/**
	 * The number of fake users to make
	 */
	const NUM_USERS = 25;

    /**
     * Run the database seeds.
     *
     * @return void
     */
	public function run()
	{
		factory('App\User', self::NUM_USERS)->create();
	}
}

?>