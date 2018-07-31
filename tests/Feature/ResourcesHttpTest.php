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
		$new_resource = factory(Resource::class)->make();
		$user = User::find($new_resource->author_id);
		$new_resource_content = factory(ResourceContent::class)->make([
			'resource_id' => $new_resource->id
		]);
		// make a request to create a new resource
		$response = $this->actingAs($user)->post('/resources/', [
			'name' => $new_resource->name,
			'use_id' => $new_resource->use_id,
			'contents' => [
				[
					'name' => $new_resource_content->name,
					'type' => $new_resource_content->type,
					'content' => $new_resource_content->content
				]
			]
		]);
		// check: do we have a new Resource and a new ResourceContent?
		$this->assertTrue(Resource::count()-1 == $resource_count);
		$this->assertTrue(ResourceContent::count()-1 == $resource_content_count);
		// delete the resource we just created
		$response = $this->actingAs($user)->delete('/resources/'.(Resource::latest()->first()->id));
		// is the Resource and its ResourceContent gone?
		$this->assertTrue(Resource::count() == $resource_count);
		$this->assertTrue(ResourceContent::count() == $resource_content_count);
	}
}
