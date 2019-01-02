<?php

namespace Tests\Feature;

use App\User;
use Tests\TestCase;
use App\Academic_Class;
use App\Repositories\ClassRepository;
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
		$parent = Academic_Class::inRandomOrder()->take(1)->get()->first();
		$response = $this->actingAs($user)->post('/classes/',
			[
				'name' => $class->name,
				'parent' => $parent->id
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

		// attach this class to the root class
		// and attach some children to this class
		$parent = 0;
		$children = ClassRepository::getTopLevelClasses()->pluck('id')->toArray();
		$response = $this->actingAs($user)->patch('/classes/attach/'.($new_class->id),
			[
				'children' => $children,
				'parent' => $parent
			]
		);
		$new_class = Academic_Class::latest()->first();
		$new_parent = $new_class->parent()->get()->first();
		$new_children = $new_class->children()->get()->pluck('id')->toArray();
		// check: was the attachment successful?
		$response->assertSuccessful();
		$this->assertNull($new_parent);
		$this->assertContains($new_class->id, ClassRepository::getTopLevelClasses()->pluck('id')->toArray());
		$this->assertEquals(array_values($children), array_values($new_children));

		// attach those children back to the root
		$response = $this->actingAs($user)->patch('/classes/attach/0',
			[
				'children' => $children
			]
		);
		$new_class = Academic_Class::latest()->first();
		// check: was the attachment successful?
		$response->assertSuccessful();
		$this->assertEquals(
			array_values(array_merge($children, [$new_class->id])),
			array_values(
				ClassRepository::getTopLevelClasses()->pluck('id')->toArray()
			)
		);
		$this->assertEmpty($new_class->children()->get());

		// attach this class to a parent class
		$parent = Academic_Class::inRandomOrder()->take(1)->get()->first();
		$response = $this->actingAs($user)->patch('/classes/attach/'.($new_class->id),
			[
				'parent' => $parent->id
			]
		);
		$new_class = Academic_Class::latest()->first();
		$new_parent = $new_class->parent()->get()->first();
		// check: was the attachment successful?
		$response->assertSuccessful();
		$this->assertEquals($new_parent->id, $parent->id);
		$this->assertNotContains($new_class->id, ClassRepository::getTopLevelClasses()->pluck('id')->toArray());

		// delete the class we created
		$response = $this->actingAs($user)->delete('/classes/'.($new_class->id));
		// is the class gone?
		$response->assertSuccessful();
		$this->assertEquals(Academic_Class::count(), $class_count);
	}
}
