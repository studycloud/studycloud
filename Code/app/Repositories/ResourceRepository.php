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
		// to do: possibly reduce this to one query? it currently executes two
		// it wouldn't be a huge performance boost. not really worth my time
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
	public static function attachTopics($resource, $new_topics)
	{
		// get the ids of the topics that we can't attach
		$disallowed_topics = self::disallowedTopics($resource)->pluck('id');
		// iterate through each topic that we want to attach and make sure it can be added
		$notAllowed = false;
		foreach ($new_topics as $topic) {
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

		$disallowed_topics = self::disallowedTopics($resource);
		$allowed = !$disallowed_topics->contains('id', $newTopic->id);
		

		if ($allowed)
		{
			self::attachTopics($resource, collect([$newTopic]));
		}
		else
		{
			$this->removeFamily($resource, $newTopic->id, $disallowed_topics);
			self::attachTopics($newTopic, $resource);
			
		}
	}

//Check to see if this actually works
//Detaches any relatives of $resource that are related to $newTopic. 
//aka NO INCEST--make sure an ancestor and a descendant topic do not share the same resource

	/**
	 * remove any ancestor or descendant topics that conflict
	 * with the topic that we want to add a resource to
	 * @param  App\Resource $resource          the resource with potential topic conflicts
	 * @param  Illuminate\Database\Eloquent\Collection $new_topic_id          the topic we want to add this resource to
	 * @param  Illuminate\Database\Eloquent\Collection $disallowed_topics a collection of topics that can't be added to our resource
	 * @return
	 */
	private function removeFamily($resource, $new_topic_id, $disallowed_topics)
	{
		$old_topics = collect();
		$topics = $resource->topics()->get();
		foreach ($topics as $topic) {
			$topic_id = $topic->id;
			if ($this->isAncestor($topic_id, $new_topic_id, $disallowed_topics)
				|| $this->isDescendant($topic_id, $new_topic_id, $disallowed_topics))
			{
				$old_topics->push($topic_id);
			}
		}
		dd($old_topics);
		// $this->detachTopics($resource, $old_topics);
	}

	/**
	 * given a portion of the tree, check to see whether $ancestor_topic_id is an ancestor of $topic_id
	 * @param  int  $topic_id           the descendant topic
	 * @param  int  $ancestor_topic_id   the ancestor to search for
	 * @param  Illuminate\Database\Eloquent\Collection  $disallowed_topics a portion of the tree to traverse
	 * @return boolean                  whether $ancestor_topic_id is an ancestor of $topic
	 */
	public function isAncestor($topic_id, $ancestor_topic_id, $disallowed_topics)
	{
		// base case: ancestor_topic is an ancestor of topic if they are the same
		if ($topic_id == $ancestor_topic_id)
		{
			return true;
		}
		// get the topic collections in $disallowed_topics with ids equal to $topic_id
		$topics = $disallowed_topics->where('id', $topic_id);
		$isAncestor = false;
		// call isAncestor() with each of the topics
		// and then OR all of the results together to get a final value
		foreach ($topics as $topic)
		{
			if ($topic->has('pivot'))
			{
				// is the parent of this $topic an ancestor of $ancestor_topic_id?
				$isAncestor = $isAncestor || $this->isAncestor($topic['pivot']['parent_id'], $ancestor_topic_id, $disallowed_topics);
			}
		}
		return $isAncestor;
	}

	/**
	 * given a portion of the tree, check to see whether $descendant_topic_id is an descendant of $topic_id
	 * @param  int  $topic_id           the ancestor topic
	 * @param  int  $descendant_topic_id   the descendant to search for
	 * @param  Illuminate\Database\Eloquent\Collection  $disallowed_topics a portion of the tree to traverse
	 * @return boolean                  whether $descendant_topic_id is an descendant of $topic
	 */
	public function isDescendant($topic_id, $descendant_topic_id, $disallowed_topics)
	{
		// base case: descendant_topic is an descendant of topic if they are the same
		if ($topic_id == $descendant_topic_id)
		{
			return true;
		}
		// get the topic collections in $disallowed_topics with ids equal to $topic_id
		$topics = $disallowed_topics->filter(
			function ($topic) use ($topic_id)
			{
				if ($topic->has('pivot'))
				{
					return $topic['pivot']['parent_id'] == $topic_id;
				}
				else
				{
					return false;
				}
			}
		);
		echo 'hi';
		dd($topics);
		$isDescendant = false;
		// call isDescendant() with each of the topics
		// and then OR all of the results together to get a final value
		foreach ($topics as $topic)
		{
			// is the parent of this $topic a descendant of $descendant_topic_id?
			$isDescendant = $isDescendant || $this->isDescendant($topic['id'], $descendant_topic_id, $disallowed_topics);
		}
		return $isDescendant;
	}
















	// private function removeFamily($newTopic, $resource, $disallowedTopics){

	// 	//Getting the pivot of topic ID
	// 	$newTopicId = $newTopic->get("id");
	// 	$disTopicId = $disallowedTopics->pluck("id");
	// 	$idx = $disTopicId->search($newTopicId, true); // The true field enables a direct comparison between integers
	// 	$newTopicPivot = $disallowedTopics[$idx]->get("pivot");

	// 	$relativeResource = checkRelatives($newTopicPivot, $resource, $disallowedTopics);



	// }

	// private function checkRelatives($newTopicPivot, $resource, $disallowedTopics){
	// 	$newTopicParent = $newTopicPivot->parent_id;
	// 	$newTopicSelf = $newTopicPivot->topic_id;

	// 	foreach($topic as $disallowedTopics){
	// 		$currentTopicParent = $topic->get("pivot")->get("parent_id");
	// 		$currentTopicSelf = $topic->get("pivot")->get("topic_id");
	// 		if($currentTopicParent == $newTopicSelf){
	// 			$resourceTopics = $resource->topics->get();
	// 			return parseChildren($topic, $resourceTopics, $disallowedTopics);
	// 		}
	// 		elseif($currentTopicSelf == $newTopicParent){
	// 			$resourceTopics = $resource->topics->get();
	// 			return parseParents($topic, $resourceTopics, $disallowedTopics);

	// 		}
	// 	}
	// 	return null;
	// }

	// private function parseChildren($topic, $resourceTopics, $disallowedTopics){
	// 	//End result: compare the topic of the recursion to the 
	// 	$topicId = $topic->get("id");
	// 	$resourceId = $resourceTopics->pluck("id");
	// 	$idx = $resourceId->search($topicId);
	// 	if($idx){
	// 		return $resourceTopics[$idx];
	// 	}
	// 	else{
	// 		//The next three lines are the same plucking code that was used in the removeFamily function.
	// 		$disTopicId = $disallowedTopics->pluck("id");
	// 		$idx = $disTopicId->search($topicId);
	// 		$disTopicPivot = $disallowedTopics[$idx]->get("pivot")->get("topic_id");
	// 		//Looking for parent that has the same topic id
	// 		foreach($topic as $disallowedTopics){
	// 			$parentIdPivots = $topic->get("pivot")->get("parent_id");
	// 		}
	// 	}	
	// }
	// private function parseParents($topic, $resourceTopics, $disallowedTopics){

	// }

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
	public static function disallowedTopics($resource)
	{
		$topics = $resource->topics()->get();
		$disallowed_topics = $topics->map(
			function ($topic)
			{
				// return the topic as a collection without its pivot attribute
				return collect($topic)->except('pivot');
			}
		);
		foreach ($topics as $topic) {
			// this resource can't be added to the ancestors or descendants of any of the topics it's already in
			// adding to an ancestor is redundant information
			// so the resource must be removed from a topic before the resource can be added to one of that topic's descendants
			$ancestors = (new TopicRepository)->ancestors($topic);
			$descendants = (new TopicRepository)->descendants($topic);
			$disallowed_topics = $disallowed_topics->merge($ancestors)->merge($descendants);
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
	public static function allowedTopics($resource)
	{
		return collect(Topic::whereNotIn('id', self::disallowedTopics($resource)->pluck('id'))->get());
	}
}