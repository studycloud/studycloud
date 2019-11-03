<?php

/*
|--------------------------------------------------------------------------
| Model Factories
|--------------------------------------------------------------------------
|
| Here you may define all of your model factories. Model factories give
| you a convenient way to create models for testing and seeding your
| database. Just tell the factory how a default model should look.
|
*/

// User Factory
$factory->define(App\User::class, function (Faker\Generator $faker)
{
	static $password;

	return [
		'fname' => $faker->firstName,
		'lname' => $faker->lastName,
		'email' => $faker->unique()->safeEmail,
		'password' => $password ?: $password = bcrypt('password'),
		'remember_token' => str_random(10),
		'type' => $faker->randomElement(App\User::getPossibleTypes())
	];
});

// Academic_Class Factory
$factory->define(App\Academic_Class::class, function (Faker\Generator $faker)
{
	return [
		'name' => $faker->text($maxNbChars = 46),
		'author_id' => $faker->randomElement(
			// get all user id's
			// but also allow some of them to be null
			array_merge(App\User::pluck('id')->toArray(), [null])
		)
	];
});

// Topic Factory
$factory->define(App\Topic::class, function (Faker\Generator $faker)
{
	return [
		'name' => ucwords(
			$faker->words($nb = 3, $asText = true)
		),
		'author_id' => $faker->randomElement(
			App\User::pluck('id')->toArray()
		)
	];
});

// Resource Content Factory
$factory->define(App\ResourceContent::class, function (Faker\Generator $faker)
{
	return [
		'name' => ucwords(
			$faker->words($nb = 3, $asText = true)
		),
		'type' => $faker->randomElement(App\ResourceContent::getPossibleTypes()),
		'content' => function(array $resource_content) use ($faker)
		{
			if ($resource_content['type'] == "link")
			{
				return $faker->url;
			}
			else
			{
				return $faker->paragraph;
			}
		},
		'resource_id' => 0, // this will get overridden by the ResourcesTableSeeder
	];
});

// Resource Factory
$factory->define(App\Resource::class, function (Faker\Generator $faker)
{
	return [
		'name' => ucwords(
			$faker->words($nb = 3, $asText = true)
		),
		'author_id' => $faker->randomElement(
			App\User::pluck('id')->toArray()
		),
		'use_id' => $faker->randomElement(
			App\ResourceUse::pluck('id')->toArray()
		)
	];
});

// Topic-Parent Factory
// This model doesn't have a factory definition. All of it's seeding happens in the TopicParentTableSeeder.

// Resource-Topic Factory
// This model doesn't have a factory definition. All of it's seeding happens in the ResourceTopicTableSeeder.
