<?php

namespace App\Repositories;

use App\Resource;

class ResourceRepository
{
	/**
	 * a wrapper function for attaching topics to prevent disallowedTopics from being added
	 * @param  App\Resource $resource the resource to attach topics to
	 * @param  Illuminate\Database\Eloquent\Collection $new_topics the topics to be attached
	 * @return 
	 */
	public function attachTopics($resource, $new_topics)
	{
		$topics_are_allowed = $new_topics->every(function($topic)
		{
			return $this->allowedTopics()->contains($topic);
		});
		if ($topics_are_allowed)
		{
			return $this->topics()->attach($new_topics);
		}
		else
		{
			throw new \Exception("One of the desired topics cannot be attached because it is an ancestor or descendant of one of this resource's current topics. You can use the allowedTopics() method to see which topics can be attached to this resource.");
		}
	}

	/**
	 * Moves the resource into the desired topic newTopic. If attempting to 
	 * move into a topic that is an ancestor or child of the resource's
	 * current topics, the current topic will be replaced by the newTopic.
	 * 
	 * @param 
	 * @return
	 * 
	 */
//check to see if this actually works 
	public function moveTopics($newTopic)
	{	

		$disallowedTopics = $this->disallowedTopics();
		$allowed = !$disallowedTopics->contains($newTopic);
		

		if($allowed){
			$this->attachTopics($newTopic);
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

	public function detachTopics($old_topics)
	{
		return $this->topics()->detach($old_topics);
	}

	/**
	 * which topics isn't this resource allowed to be added to?
	 * @return Illuminate\Database\Eloquent\Collection
	 */
	public function disallowedTopics()
	{
		$topics = $this->topics()->get();
		$disallowed_topics = $topics;
		foreach ($topics as $topic) {
			// this resource can't be added to the ancestors or descendants of any of the topics it's already in
			// adding to an ancestor is redundant information
			// so the resource must be removed from a topic before the resource can be added to one of that topic's descendants
			$disallowed_topics = $disallowed_topics->merge($topic->ancestors())->merge($topic->descendants());
		}
		return $disallowed_topics;
	}

	/**
	 * which topics is this resource allowed to be added to?
	 * @return Illuminate\Database\Eloquent\Collection
	 */
	public function allowedTopics()
	{
		return Topic::all()->diff($this->disallowedTopics());
	}
}