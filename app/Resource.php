<?php

namespace App;

use App\User;
use App\Topic;
use App\ResourceUse;
use Illuminate\Database\Eloquent\Model;

class Resource extends Model
{
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
}
