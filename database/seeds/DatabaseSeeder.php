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
			// the order of these seeders is important because several of them depend on each other, so change it with care
			$this->call('UsersTableSeeder');
			$this->call('ClassesTableSeeder');
			$this->call('ClassParentTableSeeder');
			$this->call('TopicsTableSeeder');
			$this->call('ResourcesTableSeeder');
			$this->call('ResourceContentsTableSeeder');
			$this->call('TopicParentTableSeeder');
			$this->call('ResourceClassTableSeeder');
			$this->call('ResourceTopicTableSeeder');
			$this->call('RoleUserTableSeeder');
		}
	}

	public function runCore()
	{
		/*
		| Here we define seeders for the "core" models of the app.
		| This data needs to be filled before the site starts running because the site relies on them to work properly.
		| You can think of these as settings for the site.
		| Note that the nature of these seeders means that they
		| don't have corresponding factories.
		*/
		$this->call('ResourceUsesTableSeeder');
		//$this->call('RolesTableSeeder');
		//$this->call('PermissionsTableSeeder');
		$this->call('PermissionRoleTableSeeder');
	}
}