<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Resource extends Model
{
	/**
	 * The attributes that are mass assignable.
	 *
	 * @var array
	 */
	protected $fillable = ['name', 'author_id', 'use_id'];

    protected $appends = ['target'];

    protected $hidden = ['target'];
 
  	/**
 	 * Add a unique id attribute so that JavaScript can distinguish between different models
 	 * @return string the string representing the unique id
 	 */
    public function getTargetAttribute()
    {
        return "r".($this->attributes['id']);
    }
	
	/**
	 * define the one-to-many relationship between a resource and its contents
	 * @return \Illuminate\Database\Eloquent\Relations\HasMany the relationship accessor
	 */
	public function contents()
	{
		return $this->hasMany(ResourceContent::class);
	}

	/**
	 * define the many-to-many relationship between resources and the topics they belong to
	 * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany the relationship accessor
	 */
	private function topics()
	{
		return $this->belongsToMany(Topic::class, 'resource_topic', 'resource_id', 'topic_id');
	}

	public function getTopics()
	{
		return $this->topics()->get();
	}

	/**
	 * a wrapper function for the attaching topics to prevent disallowedTopics from being added
	 * @param  Illuminate\Database\Eloquent\Collection $new_topics the topics to be attached
	 * @return 
	 */
	public function attachTopics($new_topics)
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
