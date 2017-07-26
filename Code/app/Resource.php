<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Resource extends Model
{
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
}
