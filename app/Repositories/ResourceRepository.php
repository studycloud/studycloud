<?php

namespace App\Repositories;

use App\Resource;
use App\Topic;
use App\Repositories\TopicRepository;
use Illuminate\Database\Eloquent\Collection;
use App\Helpers\NodesAndConnections;

class ResourceRepository
{
	/**
	 * In a single query, get the resources of all of the topics in a collection of topic ids.
	 * @param  array 	$topic_ids 	the topic ids of the resources you want returned
	 * @return Collection         	the resources as Collections
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
	 * a wrapper function for attaching topics that prevents disallowed topics
	 * from being added
	 * @param  Resource 	$resource 	the resource to attach topics to
	 * @param  Collection 	$new_topics the topics to be attached
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
	 * Moves the resource into the desired topic $new_topic. If attempting to 
	 * move into a topic that is an ancestor or child of the resource's
	 * current topics, the current topic will be replaced by the new_topic.
	 * @param Topic 	$new_topic	the topic which we want to add to $resource
	 * @param Resource	$resource	the resource to move
	 */
	public static function addTopic(Topic $new_topic, Resource $resource) //should this be static? added 8/30/2018
	{
		// get the set of disallowed topics for this resource
		$disallowed_topics = self::disallowedTopics($resource);
		// check whether $new_topic is one of the disallowed topics
		if ($disallowed_topics->contains('id', $new_topic->id))
		{
			// remove any current topics that are preventing $new_topic from
			// being added. no incest!
			$this->removeFamily($resource, $new_topic->id, $disallowed_topics);
		}
		// safely add $new_topic to $resource
		// in the future, you might want to change this to be a regular attach
		// (so that the function becomes faster)
		self::attachTopics($resource, collect([$new_topic]));
	}

	/**
	 * remove any ancestor or descendant topics that conflict
	 * with the topic that we want to add a resource to
	 * @param  Resource 	$resource 			the resource with potential topic conflicts
	 * @param  Collection 	$new_topic_id 		the topic we want to add this resource to
	 * @param  Collection 	$disallowed_topics 	a collection of topics that can't be added to our resource
	 */
	private function removeFamily($resource, $new_topic_id, $disallowed_topics)
	{
		$old_topics = collect();
		// convert $disallowed_topics to a collection of connections
		$disallowed_topics = NodesAndConnections::treeAsConnections($disallowed_topics);
		// which topics is this resource currently in?
		$topics = $resource->topics()->get();
		// iterate through the current topics and determine whether they conflict with the new topic
		foreach ($topics as $topic) {
			if (TopicRepository::isAncestor($topic->id, $new_topic_id, $disallowed_topics)
				|| TopicRepository::isDescendant($topic->id, $new_topic_id, $disallowed_topics))
			{
				// if a current topic conflicts, add it to the list of topics to remove
				// TODO: remove it right away and recalculate $disallowed_topics?
				$old_topics->push($topic->id);
			}
		}
		self::detachTopics($resource, $old_topics);
	}

	/**
	 * a wrapper function for detaching topics for ease of use
	 * @param  Resource 	$resource 	the resource whose topics you'd like to detach
	 * @param  Collection 	$new_topics the topics to detach
	 */
	public static function detachTopics($resource, $old_topics) 
	{
		return $resource->topics()->detach($old_topics);
	}

	/**
	 * which topics isn't this resource allowed to be added to?
	 * @param  Resource 	$resource 	the resource whose disallowedTopics you'd like to get
	 * @return Collection
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
	 * @param  Resource 	$resource 	the resource whose allowedTopics you'd like to get
	 * @return Collection
	 */
	public static function allowedTopics($resource)
	{
		return collect(Topic::whereNotIn('id', self::disallowedTopics($resource)->pluck('id'))->get());
	}
}