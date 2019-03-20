<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Academic_Class;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;

class ClassAttachmentHttpTest extends TestCase
{
	/**
	 * A basic test example.
	 *
	 * @return void
	 */
	public function testExample()
	{
		// create a subtree to run tests on
		$classes = $this->createTree();

		// run some tests
		$this->assertTrue(true);

		// delete the subtree we created earlier
		$this->deleteTree($classes);
	}

	/**
	 * create a small subtree under a single node and add it to the database
	 * @return  Collection   the classes that were created
	 */
	private function createTree()
	{
		// make 10 new classes and add them to the database
		$classes = factory(Academic_Class::class, 10)->create();
		// attach the classes so that they form a subtree under the root:
		/**
		 	  root
		 	     \
		 	      0
		 	     / \
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
