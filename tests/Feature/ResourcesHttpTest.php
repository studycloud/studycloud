<?php

namespace Tests\Feature;

use App\User;
use App\Resource;
use Tests\TestCase;
use App\ResourceUse;
use App\Academic_Class;
use App\ResourceContent;
use App\Repositories\ResourceRepository;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;

class ResourcesHttpTest extends TestCase
{
	/**
	 * Test that CRUD routes for a Resource work correctly. Assumes you've already seeded the users table.
	 *
	 * @return void
	 */
	public function testAllRoutes()
	{
		// how many Resources and ResourceContents do we have?
		$resource_count = Resource::count();
		$content_count = ResourceContent::count();
		// make a new resource but don't add it to database yet
		$resource = factory(Resource::class)->make();
		$user = User::find($resource->author_id);
		$content = factory(ResourceContent::class)->make(
			[
				'resource_id' => $resource->id
			]
		);

		// make a request to create a new resource
		// but first, get a class to attach it to
		$class = ResourceRepository::allowedClasses(null)->random();
		$response = $this->actingAs($user)->post('/resources/',
			[
				'name' => $resource->name,
				'use_id' => $resource->use_id,
				'class_id' => $class->id,
				'contents' => [
					[
						'name' => $content->name,
						'type' => $content->type,
						'content' => $content->content
					]
				]
			]
		);
		$new_resource = Resource::latest()->first();
		$new_content = $new_resource->contents->first();
		// check: do we have a new Resource and a new ResourceContent?
		$response->assertSuccessful();
		$this->assertEquals(Resource::count()-1, $resource_count);
		$this->assertEquals(ResourceContent::count()-1, $content_count);
		// check: is the name and content what we expect?
		$this->assertEquals($resource->name, $new_resource->name);
		$this->assertEquals($content->name, $new_content->name);
		// check: is the class what we expect?
		$this->assertEquals($class->id, $new_resource->class_id);

		// edit the created resource
		$new_name = "Test Resource";
		$response = $this->actingAs($user)->patch('/resources/'.($new_resource->id),
			[
				'name' => $new_name
			]
		);
		$new_resource = Resource::latest()->first();
		// check: was the edit successful?
		$response->assertSuccessful();
		$this->assertEquals($new_name, $new_resource->name);

		// edit the resource's contents
		$new_content_name = "Test Resource Content";
		$response = $this->actingAs($user)->patch('/resources/'.($new_resource->id),
			[
				'contents' => [
					[
						'id' => $new_content->id,
						'name' => $new_content_name
					]
				]
			]
		);
		$new_content = Resource::latest()->first()->contents->first();
		// check: was the edit successful?
		$response->assertSuccessful();
		$this->assertEquals($new_content_name, $new_content->name);

		// attach the resource to some items in the tree
		$class = ResourceRepository::allowedClasses($new_resource)->random();
		$response = $this->actingAs($user)->patch('/resources/attach/'.($new_resource->id),
			[
				'class' => $class->id
			]
		);
		$new_class = Resource::latest()->first()->class()->get()->first();
		// check: was the attachment successful?
		$response->assertSuccessful();
		$this->assertEquals($class->id, $new_class->id);

		// detach the resource from some items in the tree
		$response = $this->actingAs($user)->patch('/resources/detach/'.($new_resource->id),
			[
				'class' => true
			]
		);
		$new_class = Resource::latest()->first()->class()->get()->first();
		// check: was the detachment successful?
		$response->assertSuccessful();
		$this->assertEmpty($new_class);

		// delete the resource we created
		$response = $this->actingAs($user)->delete('/resources/'.($new_resource->id));
		// is the Resource and its ResourceContent gone?
		$response->assertSuccessful();
		$this->assertEquals(Resource::count(), $resource_count);
		$this->assertEquals(ResourceContent::count(), $content_count);
	}
}
