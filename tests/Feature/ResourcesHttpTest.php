<?php

namespace Tests\Feature;

use App\User;
use App\Resource;
use Tests\TestCase;
use App\ResourceUse;
use App\ResourceContent;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;

class ResourcesHttpTest extends TestCase
{
	/**
	 * Test that routes for a Resource work correctly. Assumes you've already seeded the DB.
	 *
	 * @return void
	 */
	public function testAllRoutes()
	{
		// how many Resources and ResourceContents do we have?
		$resource_count = Resource::count();
		$resource_content_count = ResourceContent::count();
		// make a new resource but don't add it to database
		$resource = factory(Resource::class)->make();
		$user = User::find($resource->author_id);
		$resource_content = factory(ResourceContent::class)->make(
			[
				'resource_id' => $resource->id
			]
		);

		// make a request to create a new resource
		$response = $this->actingAs($user)->post('/resources/',
			[
				'name' => $resource->name,
				'use_id' => $resource->use_id,
				'contents' => [
					[
						'name' => $resource_content->name,
						'type' => $resource_content->type,
						'content' => $resource_content->content
					]
				]
			]
		);
		$new_resource = Resource::latest()->first();
		// check: do we have a new Resource and a new ResourceContent?
		$response->assertSuccessful();
		$this->assertEquals(Resource::count()-1, $resource_count);
		$this->assertEquals(ResourceContent::count()-1, $resource_content_count);
		// check: is the name what we expect?
		$this->assertEquals($resource->name, $new_resource->name);

		// edit the created resource
		$new_name = "Test Resource";
		$response = $this->actingAs($user)->patch('/resources/'.($new_resource->id),
			[
				'name' => $new_name
			]
		);
		$new_resource = Resource::latest()->first();
		// check was the edit successful?
		$response->assertSuccessful();
		$this->assertEquals($new_name, $new_resource->name);

		// delete the resource we just created
		$response = $this->actingAs($user)->delete('/resources/'.($new_resource->id));
		// is the Resource and its ResourceContent gone?
		$response->assertSuccessful();
		$this->assertEquals(Resource::count(), $resource_count);
		$this->assertEquals(ResourceContent::count(), $resource_content_count);
	}
}
