<?php

namespace Tests\Feature;

use App\User;
use Tests\TestCase;
use App\Academic_Class;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;

class ClassesHttpTest extends TestCase
{
	/**
	 * Test that CRUD routes for a Resource work correctly. Assumes you've already seeded the users table.
	 *
	 * @return void
	 */
	public function testAllRoutes()
	{
		// how many Classes do we have?
		$class_count = Academic_Class::count();
		// make a new class but don't add it to database yet
		$class = factory(Academic_Class::class)->make();
		$user = User::find($class->author_id);

		// make a request to create a new class
		// but first, pick a class to be our parent
		$parents = Academic_Class::inRandomOrder()->take(1)->get();
		$response = $this->actingAs($user)->post('/classes/',
			[
				'name' => $class->name,
				'parent' => $parents[0]->id
			]
		);
		$new_class = Academic_Class::latest()->first();
		// check: do we have a new Class?
		$response->assertSuccessful();
		$this->assertEquals(Academic_Class::count()-1, $class_count);
		// check: is the name what we expect?
		$this->assertEquals($class->name, $new_class->name);

		// edit the created class
		$new_name = "Test Class";
		$response = $this->actingAs($user)->patch('/classes/'.($new_class->id),
			[
				'name' => $new_name
			]
		);
		$new_class = Academic_Class::latest()->first();
		// check: was the edit successful?
		$response->assertSuccessful();
		$this->assertEquals($new_name, $new_class->name);

		// delete the class we created
		$response = $this->actingAs($user)->delete('/classes/'.($new_class->id));
		// is the Class gone?
		$response->assertSuccessful();
		$this->assertEquals(Academic_Class::count(), $class_count);
	}
}
