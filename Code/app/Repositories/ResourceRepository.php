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
	public static function getByTopics($topic_ids)
	{
		// TODO: possibly reduce this to one query? it currently executes two
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
			throw new \Exception("One of the desired topics cannot be attached because it is an ancestor or descendant of one of this resource's current topics. You can use the disallowedTopics() method to see which topics cannot be attached to this resource.");
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
	public function moveTopics($newTopic, $resource)
	{	

		$disallowedTopics = $this->disallowedTopics($resource);
		$allowed = !$disallowedTopics->contains($newTopic);
		

		if($allowed){
			$this->attachTopics($resource, $newTopic);
		}
		elseif(!$allowed)
		{
			$this->removeFamily($newTopic, $resource, $disallowedTopics);
			$this->attachTopics($newTopic, $resource);
			
		}
	}

//Check to see if this actually works
//Detaches any relatives of $resource that are related to $newTopic. 
//aka NO INCEST--make sure an ancestor and a descendant topic do not share the same resource
	private function removeFamily($newTopic, $resource, $disallowedTopics){


		$relativeResource = checkRelatives($newTopic, $resource, $disallowedTopics);



	}

	private function checkRelatives($newTopic, $resource, $disallowedTopics){
		$newTopicParent = $newTopic->pivot->parent_id;
		$newTopicSelf = $newTopic->pivot->topic_id;

		foreach($topic as $disallowedTopics){
			$currentTopicParent = $topic->get("pivot")->get("parent_id");
			$currentTopicSelf = $topic->get("pivot")->get("topic_id");
			if($currentTopicParent == $newTopicSelf){
				$resourceTopics = $resource->topics->get();
				return parseChildren($topic, $resourceTopics, $disallowedTopics);
			}
			elseif($currentTopicSelf == $newTopicParent){
				$resourceTopics = $resource->topics->get();
				return parseParents($topic, $resourceTopics, $disallowedTopics);

			}
		}
		return null;
	}

	private function parseChildren($topic, $resourceTopics, $disallowedTopics){

		$topic_id = $topic->get("pivot")->get("topic_id");
		// foreach
		

	}
	private function parseParents($topic, $resourceTopics, $disallowedTopics){

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
				return collect($topic);
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
					return $topic;
				}
			);
			$descendants = (new TopicRepository)->descendants($topic->id)->get("nodes")->map(
				function ($topic)
				{
					// return the topic as a collection without its pivot attribute
					return $topic;
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