<?php

namespace Tests\Feature;

use App\User;
use Tests\TestCase;
use App\Academic_Class;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;

class ClassAttachmentHttpTest extends TestCase
{
	/**
	 * Perform tests on the class attachment route: classes.attach
	 *
	 * @return void
	 */
	public function testClassAttachRoute()
	{
		// retrieve a random user to run the tests with
		$user = User::inRandomOrder()->first();

		// create a subtree to run tests on:
		/**
			  root
			  /  \
		 other    0
		classes  / \
			    1   2
			   /|\  |\
			  3 4 5 6 7
			   / \
			  8   9
		*/
		$classes = $this->createTree($user);

		// TEST 1: basic test
		// attach class 4 as a parent of class 1
		// this should work because the parent of class 4 already is class 1
		$response = $this->actingAs($user)->patch('/classes/attach/'.($classes[4]->id),
			[
				'parent' => $classes[1]->id
			]
		);
		// check: was the attachment successful?
		$response->assertSuccessful();
		$this->assertEquals(Academic_Class::find($classes[4]->id)->parent->id, $classes[1]->id);

		// TEST 2: basic test w/ children now
		// attach class 8 as a child of class 4
		// this should work because class 8 already is a child of class 4
		$response = $this->actingAs($user)->patch('/classes/attach/'.($classes[4]->id),
			[
				'children' => [$classes[8]->id]
			]
		);
		// check: was the attachment successful?
		$response->assertSuccessful();
		$this->assertEquals(Academic_Class::find($classes[8]->id)->parent->id, $classes[4]->id);

		// TEST 3: normal parent attach
		// make class 2 the parent of class 4
		$response = $this->actingAs($user)->patch('/classes/attach/'.($classes[4]->id),
			[
				'parent' => $classes[2]->id
			]
		);
		// check: was the attachment successful?
		$response->assertSuccessful();
		$this->assertEquals(Academic_Class::find($classes[4]->id)->parent->id, $classes[2]->id);

		// TEST 4: normal root attach
		// attach class 4 as a child of the root class
		$response = $this->actingAs($user)->patch('/classes/attach/0',
			[
				'children' => [$classes[4]->id]
			]
		);
		// check: was the attachment successful?
		$response->assertSuccessful();
		$this->assertNull(Academic_Class::find($classes[4]->id)->parent);

		// TEST 5: normal child attach
		// make class 4 the child of class 1 again
		$response = $this->actingAs($user)->patch('/classes/attach/'.($classes[1]->id),
			[
				'children' => [$classes[4]->id]
			]
		);
		// check: was the attachment successful?
		$response->assertSuccessful();
		$this->assertEquals(Academic_Class::find($classes[4]->id)->parent->id, $classes[1]->id);

		// TEST 6: invalid root attach
		// try to make class 4 the parent of the root class
		// this should be rejected, since no class can be the parent of the root
		$response = $this->actingAs($user)->patch('/classes/attach/0',
			[
				'parent' => $classes[4]->id
			]
		);
		// check: was the attachment unsuccessful?
		$response->assertSessionHasErrors([
			"parent" => "The root class cannot be assigned a parent."
		]);
		$this->assertEquals(Academic_Class::find($classes[4]->id)->parent->id, $classes[1]->id);

		// TEST 7: invalid parent attach
		// try to make class 9 the parent of class 1
		// this should be rejected, since class 9 is a 2 level descendant of class 1
		$response = $this->actingAs($user)->patch('/classes/attach/'.($classes[1]->id),
			[
				'parent' => $classes[9]->id
			]
		);
		// check: was the attachment unsuccessful?
		$response->assertSessionHasErrors([
			"parent" => "Class ".$classes[9]->id." is a descendant of class ".$classes[1]->id.". It cannot be added as its parent."
		]);
		$this->assertEquals(Academic_Class::find($classes[1]->id)->parent->id, $classes[0]->id);

		// TEST 8: invalid child attach
		// try to make class 9 the parent of class 1
		// this should be rejected, since class 9 is a 2 level descendant of class 1
		$response = $this->actingAs($user)->patch('/classes/attach/'.($classes[9]->id),
			[
				'children' => [$classes[1]->id]
			]
		);
		// check: was the attachment unsuccessful?
		$response->assertSessionHasErrors([
			"children" => "Class ".$classes[1]->id." is an ancestor of class ".$classes[9]->id.". It cannot be added as its child."
		]);
		$this->assertEquals(Academic_Class::find($classes[1]->id)->parent->id, $classes[0]->id);

		// TEST 8: invalid children attach
		// try to make class 9 the parent of class 1 and class 0
		// these should both be rejected, since class 9 is a 2 level descendant of class 1 and a 3 level descendant of class 0
		$response = $this->actingAs($user)->patch('/classes/attach/'.($classes[9]->id),
			[
				'children' => [$classes[0]->id, $classes[1]->id]
			]
		);
		// check: was the attachment unsuccessful?
		$response->assertSessionHasErrors([
			"children" => "Classes ".readable_array([$classes[1]->id, $classes[0]->id])." are ancestors of class ".$classes[9]->id.". They cannot be added as its children."
		]);
		$this->assertEquals(Academic_Class::find($classes[1]->id)->parent->id, $classes[0]->id);
		$this->assertNull(Academic_Class::find($classes[0]->id)->parent);

		// TEST 9: invalid children with a valid child
		// try to make class 9 the parent of class 1, class 0, and class 5
		// only class 5 should not be rejected, since class 9 is a 2 level descendant of class 1 and a 3 level descendant of class 0
		$response = $this->actingAs($user)->patch('/classes/attach/'.($classes[9]->id),
			[
				'children' => [$classes[0]->id, $classes[1]->id, $classes[5]->id]
			]
		);
		// check: was the attachment unsuccessful?
		$response->assertSessionHasErrors([
			"children" => "Classes ".readable_array([$classes[1]->id, $classes[0]->id])." are ancestors of class ".$classes[9]->id.". They cannot be added as its children."
		]);
		$this->assertEquals(Academic_Class::find($classes[1]->id)->parent->id, $classes[0]->id);
		$this->assertNull(Academic_Class::find($classes[0]->id)->parent);
		$this->assertEquals(Academic_Class::find($classes[5]->id)->parent->id, $classes[1]->id);

		// TEST 10: valid parent and child attach
		// inject class 3 between classes 2 and 6/7
		// after this operation, class 6 and 7 will be children of class 3, and class 3 will be a child of class 2
		$response = $this->actingAs($user)->patch('/classes/attach/'.($classes[3]->id),
			[
				'parent' => $classes[2]->id,
				'children' => [$classes[6]->id, $classes[7]->id]
			]
		);
		// check: was the attachment successful?
		$response->assertSuccessful();
		$this->assertEquals(Academic_Class::find($classes[3]->id)->parent->id, $classes[2]->id);
		$this->assertEquals(Academic_Class::find($classes[6]->id)->parent->id, $classes[3]->id);
		$this->assertEquals(Academic_Class::find($classes[7]->id)->parent->id, $classes[3]->id);

		// TEST 11: invalid parent and child attach
		// make class 6 the child of class 8 but the parent of class 1
		// taken alone, each of these parent and child attachments would be ok
		// but together, they would create a cycle in the tree
		// so they should be rejected
		$response = $this->actingAs($user)->patch('/classes/attach/'.($classes[6]->id),
			[
				'parent' => $classes[8]->id,
				'children' => [$classes[1]->id]
			]
		);
		// check: was the attachment unsuccessful?
		$response->assertSessionHasErrors([
			"children" => "By making class ".$classes[8]->id." the parent of class ".$classes[6]->id.", class ".$classes[1]->id." will become an ancestor of class ".$classes[6]->id.". It cannot be added as its child."
		]);
		$this->assertEquals(Academic_Class::find($classes[1]->id)->parent->id, $classes[0]->id);
		$this->assertEquals(Academic_Class::find($classes[6]->id)->parent->id, $classes[3]->id);

		// delete the subtree we created earlier
		$this->deleteTree($classes);
	}

	/**
	 * create a small subtree under a single node and add it to the database
	 * @param   User         the author of the subtree
	 * @return  Collection   the classes that were created
	 */
	private function createTree(User $user)
	{
		// make 10 new classes and add them to the database
		$classes = factory(Academic_Class::class, 10)->create([
			'author_id' => $user->id
		]);
		// attach the classes so that they form a subtree under the root:
		/**
			  root
			  /  \
		 other    0
		classes  / \
			    1   2
			   /|\  |\
			  3 4 5 6 7
			   / \
			  8   9
		*/
		// attach classes 1 and 2 as children of class 0
		$classes[0]->children()->saveMany($classes->slice(1, 2));
		// attach classes 3, 4, and 5 as children of class 1
		$classes[1]->children()->saveMany($classes->slice(3, 3));
		// attach classes 6 and 7 as children of class 2
		$classes[2]->children()->saveMany($classes->slice(6, 2));
		// attach classes 8 and 9 as children of class 4
		$classes[4]->children()->saveMany($classes->slice(8, 2));
		// return the classes we created
		return $classes;
	}

	/**
	 * delete the classes representing a tree
	 * @param  Collection $classes the Academic_Class instances we should delete
	 */
	private function deleteTree($classes)
	{
		$classes->reverse()->each(
			function ($class)
			{
				$class->delete();
			}
		);
	}
}
