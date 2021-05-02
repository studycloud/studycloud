<?php

namespace Tests\Unit;

use App\User;
use App\ResourceUse;
use Tests\TestCase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;

class ResourceUseTest extends TestCase
{
    /**
     * A basic test example.
     *
     * @return void
     */
    public function testAllRoutes()
    {
    	// how many ResourceUses do we have?
		$resource_use_count = ResourceUse::count();
		// make a new resource_use but don't add it to database yet
		$resource_use = factory(ResourceUse::class)->make([
			'author_id' => User::pluck('id')->random()
		]);
		$user = User::find($resource_use->author_id);

		// make a request to create a new resource_use
		$response = $this->actingAs($user)->post('/resource_uses/',
			[
				'name' => $resource_use->name
			]
		);
		$new_resource_use = ResourceUse::latest()->first();
		// check: do we have a new ResourceUse?
		$response->assertSuccessful();
		$this->assertEquals(ResourceUse::count()-1, $resource_use_count);
		// check: is the name what we expect?
		$this->assertEquals($resource_use->name, $new_resource_use->name);

		// now make a request to edit the resource use by changing its name
		$new_name = "Test Resource Use";
		$response = $this->actingAs($user)->patch('/resource_uses/'.($new_resource_use->id),
			[
				'name' => $new_name
			]
		);
		// retrieve the resource use from the DB again, since we expect its name to have changed
		$new_resource_use = ResourceUse::find($new_resource_use->id);
		// check: was the edit successful?
		$response->assertSuccessful();
		$this->assertEquals($new_name, $new_resource_use->name);

		// now try deleting the resource use
		$response = $this->actingAs($user)->delete('/resource_uses/'.($new_resource_use->id));
		// is the Resource and its ResourceContent gone?
		$response->assertSuccessful();
		$this->assertEquals(ResourceUse::count(), $resource_use_count);
    }
}
