<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

use  App\Topic;

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
	public function topics()
	{
		return $this->belongsToMany(Topic::class, 'resource_topic', 'resource_id', 'topic_id');
	}

	public function getTopics()
	{
		return $this->topics()->get();
	}
}
