<?php

namespace App;

use App\User;
use App\Topic;
use App\ResourceUse;
use App\Academic_Class;
use Laravel\Scout\Searchable;
use Illuminate\Database\Eloquent\Model;

class Resource extends Model
{
	/**
	 * Use Laravel Scout's trait to make this model searchable
	 */
	use Searchable;

	/**
	 * The attributes that are mass assignable.
	 *
	 * @var array
	 */
	protected $fillable = ['name', 'use_id'];

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
	public function topics()
	{
		return $this->belongsToMany(Topic::class, 'resource_topic', 'resource_id', 'topic_id');
	}

	public function getTopics()
	{
		return $this->topics()->get();
	}

	/**
	 * define the many-to-one relationship between resources and their author
	 * @return User	the author of this resource
	 */
	public function author()
	{
		return $this->belongsTo(User::class);
	}

	/**
	 * define the many-to-one relationship between resources and their use
	 * @return ResourceUse	this resource's use
	 */
	public function use()
	{
		return $this->belongsTo(ResourceUse::class);
	}

	/**
	 * define the many-to-one relationship between resources and the classes they belong to
	 * @return \Illuminate\Database\Eloquent\Relations\BelongsTo the relationship accessor
	 */
	public function class()
	{
		return $this->belongsTo(Academic_Class::class, 'class_id');
	}

	/**
	 * Is this resource viewable by the public?
	 * @return boolean
	 */
	public function status()
	{
		return boolval($this->status);
	}

	/**
	 * Get the indexable data array for the model.
	 * @return array
	 */
	public function toSearchableArray($toArray=true)
	{
		$resource = collect();
		$resource['name'] = $this->name;
		$resource['author'] = $this->author->name();
		$resource['use'] = $this->use->name;
		$resource['class'] = $this->class->name;
		$resource['contents'] = $this->contents->map(
			function($content)
			{
				$new_content = collect($content);
				return $new_content->only(['name', 'type', 'content']);
			}
		);
		// if we must return the collection as an array:
		if ($toArray)
		{
			$resource['contents'] = $resource['contents']->toArray();
			return $resource->toArray();
		}
		else
		{
			return $resource;
		}
	}

	/**
	 * Should this resource be searchable?
	 * @return boolean
	 */
	public function shouldBeSearchable()
	{
		return $this->status();
	}
}
