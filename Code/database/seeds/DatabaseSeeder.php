<?php

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
	/**
	 * Run the database seeds.
	 *
	 * @return void
	 */
	public function run()
	{
		// first, we must seed the core material
		// note that this happens regardless of the App::environment variable
		$this->runCore();

		// now we can seed everything else as long as the App::environment variable indicates that the app is not in production or staging mode (ie testing or something else of the sort)
		if (!App::environment('production','staging'))
		{
			// the order of these seeders is important because several of them depend on each other, so don't change the order
			$this->call('UsersTableSeeder');
			$this->call('ClassesTableSeeder');
			$this->call('TopicsTableSeeder');
			$this->call('ResourcesTableSeeder');
			$this->call('ResourceContentsTableSeeder');
			$this->call('TopicParentTableSeeder');
			$this->call('ResourceTopicTableSeeder');
			$this->call('RoleUserTableSeeder');
		}
	}

	public function runCore()
	{
		/*
		| Here we define seeders for the "core" models of the app.
		| Their corresponding tables will need to be filled with
		| data before the site starts running because these models
		| represent values that are indendent of changes in
		| the site later. You can think of them as settings for
		| the app.
		| Note that the nature of these seeders means that they
		| don't have corresponding factories.
		*/
		$this->call('ResourceUsesTableSeeder');
		$this->call('RolesTableSeeder');
		$this->call('PermissionsTableSeeder');
		$this->call('PermissionRoleTableSeeder');
	}
}