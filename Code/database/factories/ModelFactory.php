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
        // TODO: update this enum to dynamically retrieve whatever enum options are in the database
        'type' => $faker->randomElement(['student','teacher'])
    ];
});

// Academic_Class Factory
$factory->define(App\Academic_Class::class, function (Faker\Generator $faker)
{
    return [
        'name' => $faker->text($maxNbChars = 46)
    ];
});

// Topic Factory
$factory->define(App\Topic::class, function (Faker\Generator $faker)
{
    return [
        'name' => ucwords(
            $faker->words($nb = 3, $asText = true)
        ),
        'author_id' => $faker->randomElement(App\User::select('id')->get()->toArray())['id']
    ];
});

// Resource Content Factory
$factory->define(App\ResourceContent::class, function (Faker\Generator $faker)
{
    return [
        'name' => ucwords(
            $faker->words($nb = 3, $asText = true)
        ),
        // TODO: update this enum to dynamically retrieve whatever enum options are in the database
        'type' => $faker->randomElement(['text', 'link', 'file']),
        'content' => $faker->paragraph,
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
        'author_id' => $faker->randomElement(App\User::select('id')->get()->toArray())['id'],
        'use_id' => $faker->randomElement(App\ResourceUse::select('id')->get()->toArray())['id']
    ];
});

// Topic-Parent Factory
// This model doesn't have a factory definition. All of it's seeding happens in the TopicParentTableSeeder.

// Resource-Topic Factory
$factory->define(App\ResourceTopic::class, function (Faker\Generator $faker)
{
    return [
        'resource_id' => 0, // this will get overridden by the ResourceTopicTableSeeder
        'topic_id' => function(array $curr_ResourceTopic)
        {
            // see the Role-User Factory code for a more detailed explanation of the code below; it's essentially the same code
            global $old_resource_id, $hacky_faker;
            if ($old_resource_id != $curr_ResourceTopic['resource_id'])
            {
                $hacky_faker = new Faker\Generator;
                $hacky_faker->addProvider(new Faker\Provider\Base($hacky_faker));
            }
            $old_resource_id = $curr_ResourceTopic['resource_id'];
            $topic_id = $hacky_faker->unique()->randomElement(
                App\Resource::find($old_resource_id)->allowedTopics()->pluck('id')->all()
            );
            return $topic_id;
        }
    ];
});

// Role-User Factory
$factory->define(App\RoleUser::class, function (Faker\Generator $faker)
{
    return [
        'user_id' => 0, // this will get overridden by the RoleUserTableSeeder
        'role_id' => function(array $curr_RoleUser)
        {
            // Disclaimer: the code below is super hacky and quite weird, but it works. If anybody can come up with a better way of doing this, please change it.
            // Until then, I'll walk you through the madness that is written here.

            // First, we declare two global variables so that their values may persist after each call to the function (unless explicity changed!) because PHP's variable scope rules say that these variables don't exist outside our function.
            global $old_user_id, $hacky_faker;
            // Now we can check whether the old user_id (from the previous call) is not equal to the current user_id of the RoleUser we are creating right now.
            // Note that the current user_id will actually get its value from the RoleUserTableSeeder (the 0 value will be overridden).
            // Essentially, this control structure defines code that should only run if we've switched users in the RoleUserTableSeeder.
            if ($old_user_id != $curr_RoleUser['user_id'])
            {
                // Create an entirely new faker generator instance; we won't rely on the one they feed to our closure, so that we can have more control over the one we're using.
                // You'll see why we have to do this later when we attempt to use the unique() provider.
                $hacky_faker = new Faker\Generator;
                // Add the base provider class.
                // Otherwise, we won't be able to call the randomElement formatter later.
                $hacky_faker->addProvider(new Faker\Provider\Base($hacky_faker));
            }
            // Now we can update the old user_id to the current user_id for the next time this function is called.
            $old_user_id = $curr_RoleUser['user_id'];
            // Here is where the magic happens!
            // We can use the randomElement formatter to pick a random role_id from the list that is available.
            // We can call the unique() provider before randomElement so that we are gauranteed to pick a different role_id each time.
            $role_id = $hacky_faker->unique()->randomElement(
                App\Role::select('id')->get()->toArray()
            )['id'];
            // Note that the unique() provider keeps a record of which role_id's have been called. This record will persist for multiple calls to the same faker generator instance.
            // Laravel apparently uses the same faker generator instance each time factory() is called for a model class. Unfortunately, this also means that unique()'s record will persist for the different user_id's for which we try to create roles. In other words, unique() will apply to all the roles that are generated for all the users instead of only the roles for each user.
            // The ideal solution would be some way to reset the record that is kept by unique() directly before we switch to generating roles for a new user. The unique() provider is supposed to do that if we pass $reset=true as a parameter to it, but this didn't actually work for me when I tried it. (It was a huge headache.)
            // To get around this, we recreate the faker generator instance for each user (see the code in the if statement above), effectively starting with a completely new slate and a completely new unique() provider that persists until we decide to create roles for a new user.
            return $role_id;
        }
    ];
});