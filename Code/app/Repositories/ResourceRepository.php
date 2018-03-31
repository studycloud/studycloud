<?php

namespace App\Repositories;

use App\Resource;
use App\Topic;
use App\Repositories\TopicRepository;

class ResourceRepository
{
	/**
	 * In a single query, get the resources of all of the topics in a collection of topic ids.
	 * @param  array $topic_ids the topic ids of the resources you want returned
	 * @return Illuminate\Database\Eloquent\Collection         the resources as Collections
	 */
	public function getByTopics($topic_ids)
	{
		// TODO: check that this actually executes only one query
		// TODO: return as nodes and connections?
		return Topic::whereIn('id', $topic_ids)->with('resources')->get()->pluck('resources')->collapse()->map(
			function ($topic)
			{
				// return the topic as a collection
				return collect($topic);
			}
		);
	}

	/**
	 * a wrapper function for attaching topics to prevent disallowedTopics from being added
	 * @param  App\Resource $resource the resource to attach topics to
	 * @param  Illuminate\Database\Eloquent\Collection $new_topics the topics to be attached
	 * @return 
	 */
	public function attachTopics($resource, $new_topics)
	{
		// get the ids of the topics that we can't attach
		$disallowed_topics = $this->disallowedTopics($resource)->pluck('id');
		// iterate through each topic that we want to attach and make sure it can be added
		$notAllowed = false;
		foreach ($topic as $new_topics) {
			$notAllowed = $notAllowed && $disallowed_topics->contains($topic->id);
		}
		// if any of the topics can't be added
		if ($notAllowed)
		{
			throw new \Exception("One of the desired topics cannot be attached because it is an ancestor or descendant of one of this resource's current topics. You can use the allowedTopics() method to see which topics can be attached to this resource.");
		}
		else
		{
			return $resource->topics()->attach($new_topics);
		}
	}

	/**
	 * Moves the resource into the desired topic newTopic. If attempting to 
	 * move into a topic that is an ancestor or child of the resource's
	 * current topics, the current topic will be replaced by the newTopic.
	 * @param App\Resource $resource the resource to move
	 */
//check to see if this actually works 
	public function moveTopics($resource, $newTopic)
	{	

		$disallowedTopics = $this->disallowedTopics($resource);
		$allowed = !$disallowedTopics->contains($newTopic);
		

		if($allowed){
			$this->attachTopics($resource, $newTopic);
		}
		elseif(!$allowed)
		{
			$this->removeFamily($newTopic);
			$this->attachTopics($newTopic);
			
		}
	}

//Check to see if this actually works
//aka NO INCEST--make sure an ancestor and a descendant topic do not share the same resource
	private function removeFamily($newTopic){

		$currentTopics = $this->getTopics();
		$familyMembers = $newTopic->ancestors()->merge($newTopic->descendants()); //THIS DOES NOT CHECK FOR ALL ANCESTORS/DESCENDANTS, ONLY ONE LEVEL ABOVE.
		foreach($currentTopics as $currentTopic)
		{
			foreach($familyMembers as $familyMember)
			{
				if($currentTopic == $familyMember)
				{
					$this->detachTopics($currentTopic);
				}
			}
		}
	}

	/**
	 * a wrapper function for detaching topics for ease of use
	 * @param  App\Resource $resource the resource whose topics you'd like to detach
	 * @param  Illuminate\Database\Eloquent\Collection $new_topics the Topics to detach
	 * @return 
	 */
	public function detachTopics($resource, $old_topics)
	{
		return $resource->topics()->detach($old_topics);
	}

	/**
	 * which topics isn't this resource allowed to be added to?
	 * @param  App\Resource $resource the resource whose disallowedTopics you'd like to get
	 * @return Illuminate\Database\Eloquent\Collection
	 */
	public function disallowedTopics($resource)
	{
		$topics = $resource->topics()->get();
		$disallowed_topics = $topics->map(
			function ($topic)
			{
				// return the topic as a collection without its pivot attribute
				return collect($topic)->except(['pivot']);
			}
		);
		foreach ($topics as $topic) {
			// this resource can't be added to the ancestors or descendants of any of the topics it's already in
			// adding to an ancestor is redundant information
			// so the resource must be removed from a topic before the resource can be added to one of that topic's descendants
			$ancestors = (new TopicRepository)->ancestors($topic->id)->get("nodes")->map(
				function ($topic)
				{
					// return the topic as a collection without its pivot attribute
					return $topic->except(['pivot']);
				}
			);
			$descendants = (new TopicRepository)->descendants($topic->id)->get("nodes")->map(
				function ($topic)
				{
					// return the topic as a collection without its pivot attribute
					return $topic->except(['pivot']);
				}
			);
			$disallowed_topics = $disallowed_topics->merge($ancestors)->merge($descendants)->unique();
		}
		return $disallowed_topics;
	}

	/**
	 * which topics is this resource allowed to be added to?
	 * Note: this function executes one more query than disallowedTopics() and is therefore a bit slower. don't use it if you don't have to
	 * Note also: the topics returned in this function won't have pivots
	 * @param  App\Resource $resource the resource whose allowedTopics you'd like to get
	 * @return Illuminate\Database\Eloquent\Collection
	 */
	public function allowedTopics($resource)
	{
		return collect(Topic::whereNotIn('id', $this->disallowedTopics($resource)->pluck('id'))->get());
	}
}