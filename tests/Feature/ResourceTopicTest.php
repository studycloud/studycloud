<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;

use App\Topic;
use App\Repositories\TopicRepository;
use App\Resource;

class ResourceTopicTest extends TestCase
{
    /**
     * Test whether the resources in the database are attached
     * to topics in a valid way.
     *
     * @return void
     */
    public function testValidResources()
    {
        // strategy: walk down the topic tree and check whether resources
        // appear more than once in child topics
        $this->assertEmpty($this->invalidResources());
    }

    private function invalidResources(Topic $topic = null, $resources = collect())
    {
    	if ($topic === null)
    	{
    		$children = TopicRepository::getTopLevelTopics();
    	}
    	else
    	{
    		$children = $topic->children;
    	}

    	return $children->map(
	    	function ($child) use ($this, $resources)
	    	{
	    		$child_resources = $child->resources->pluck('id');
	    		return $resources->intersect($child_resources)->merge(
	    			$this->invalidResources(
	    				$child,
	    				$resources->merge($child_resources)
	    			)
	    		)->all();
	    	}
	    )->collapse();
    }
}
