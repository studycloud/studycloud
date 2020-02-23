<?php

namespace Tests\Feature;

use App\User;
use Tests\TestCase;
use App\Notice;
use App\Repositories\ClassRepository;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;

class NoticesHttpTest extends TestCase
{
	/**
	 * Test that CRUD routes for a Resource work correctly. Assumes you've already seeded the users table.
	 *
	 * @return void
	 */
	public function testAllRoutes()
	{
		// how many Notices do we have?
		$notice_count = Notice::count();
		// make a new notice but don't add it to database yet
		$notice = factory(Notice::class)->make([
			'author_id' => User::inRandomOrder()->take(1)->get()->first()['id']
		]);
		//echo $notice;
		$user = User::find($notice->author_id);

		// make a request to create a new notice
		// but first, pick a notice to be our parent
		$parent = Notice::inRandomOrder()->take(1)->get()->first();
		$response = $this->actingAs($user)->post('/notices/',
			[
				'parent' => $parent->id,
				'description' => $notice->description,
				'link' => $notice->link,
				'priority' => $notice->priority,
				'deadline' => $notice->deadline
			]
		);
		$new_notice = Notice::latest()->first();
		// check: do we have a new notice?
		//dd($response);
		$response->assertSuccessful();
		
		$this->assertEquals(Notice::count()-1, $notice_count);
		// check: is the description what we expect?
		$this->assertEquals($notice->description, $new_notice->description);

		// delete the notice we created
		$response = $this->actingAs($user)->delete('/notices/'.($new_notice->id));
		// is the notice gone?
		$response->assertSuccessful();
		$this->assertEquals(Notice::count(), $notice_count);

	// 	// attach this class to the root class
	// 	// and attach some children to this class
	// 	$parent = 0;
	// 	$children = ClassRepository::getTopLevelClasses()->pluck('id')->toArray();
	// 	$response = $this->actingAs($user)->patch('/classes/attach/'.($new_class->id),
	// 		[
	// 			'children' => $children,
	// 			'parent' => $parent
	// 		]
	// 	);
	// 	$new_class = Academic_Class::latest()->first();
	// 	$new_parent = $new_class->parent()->get()->first();
	// 	$new_children = $new_class->children()->get()->pluck('id')->toArray();
	// 	// check: was the attachment successful?
	// 	$response->assertSuccessful();
	// 	$this->assertNull($new_parent);
	// 	$this->assertContains($new_class->id, ClassRepository::getTopLevelClasses()->pluck('id')->toArray());
	// 	$this->assertEquals(array_values($children), array_values($new_children));

	// 	// attach those children back to the root
	// 	$response = $this->actingAs($user)->patch('/classes/attach/0',
	// 		[
	// 			'children' => $children
	// 		]
	// 	);
	// 	$new_class = Academic_Class::latest()->first();
	// 	// check: was the attachment successful?
	// 	$response->assertSuccessful();
	// 	$this->assertEquals(
	// 		array_values(array_merge($children, [$new_class->id])),
	// 		array_values(
	// 			ClassRepository::getTopLevelClasses()->pluck('id')->toArray()
	// 		)
	// 	);
	// 	$this->assertEmpty($new_class->children()->get());


	}
}

