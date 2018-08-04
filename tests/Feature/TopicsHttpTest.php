<?php

namespace Tests\Feature;

use App\Topic;
use Tests\TestCase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;

class TopicsHttpTest extends TestCase
{
	/**
	 * Test that CRUD routes for a Resource work correctly. Assumes you've already seeded the users table.
	 *
	 * @return void
	 */
	public function testAllRoutes()
	{
		// how many Topics do we have?
		$topic_count = Topic::count();
		// make a new topic but don't add it to database yet
		$topic = factory(Topic::class)->make();
		$user = User::find($topic->author_id);

		// make a request to create a new topic
		$response = $this->actingAs($user)->post('/topics/',
			[
				'name' => $topic->name
			]
		);
		$new_topic = Topic::latest()->first();
		// check: do we have a new Topic?
		$response->assertSuccessful();
		$this->assertEquals(Topic::count()-1, $topic_count);
		// check: is the name what we expect?
		$this->assertEquals($topic->name, $new_topic->name);

		// edit the created topic
		$new_name = "Test Topic";
		$response = $this->actingAs($user)->patch('/topics/'.($new_topic->id),
			[
				'name' => $new_name
			]
		);
		$new_topic = Topic::latest()->first();
		// check: was the edit successful?
		$response->assertSuccessful();
		$this->assertEquals($new_name, $new_topic->name);

		// delete the topic we created
		$response = $this->actingAs($user)->delete('/topics/'.($new_topic->id));
		// is the Topic gone?
		$response->assertSuccessful();
		$this->assertEquals(Topic::count(), $topic_count);
	}
}
