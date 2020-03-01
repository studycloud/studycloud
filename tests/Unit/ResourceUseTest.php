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
    public function testResourceUseCreation()
    {
        //$this->assertTrue(true);
    // how many ResourceUses do we have?
		$resource_use_count = ResourceUse::count();
		// make a new resource_use but don't add it to database yet
		$resource_use = factory(ResourceUse::class)->make([
			'author_id' => User::inRandomOrder()->take(1)->get()->first()['id']
		]);
		$user = User::find($resource_use->author_id);

		// make a request to create a new resource_use
		$response = $this->actingAs($user)->post('/resource_uses/',
			[
				'name' => $resource_use->name
			]
		);
		//dd($response->exception);
		$new_resource_use = ResourceUse::latest()->first();
		// check: do we have a new ResourceUse?
		$response->assertSuccessful();
		$this->assertEquals(ResourceUse::count()-1, $resource_use_count);
		// check: is the name what we expect?
		$this->assertEquals($resource_use->name, $new_resource_use->name);

    }
}
