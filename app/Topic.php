<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class Topic extends Model
{
	/**
	 * The attributes that are mass assignable.
	 *
	 * @var array
	 */
	protected $fillable = ['name'];

	/**
	 * returns all topics that have this topic as their parent
	 */
	public function children()
	{
		return $this->belongsToMany(Topic::class, 'topic_parent', 'parent_id',  'topic_id');
	}

	/**
	 * returns all topics for which this topic is a child
	 */
	public function parents()
	{
		return $this->belongsToMany(Topic::class, 'topic_parent', 'topic_id',  'parent_id');
	}

	public function resources()
	{
		return $this->belongsToMany(Resource::class, 'resource_topic', 'topic_id', 'resource_id');
	}

	public function getResources()
	{
		return $this->resources()->get();
	}

	/**
	 * define the many-to-one relationship between topics and their author
	 * @return User	the author of this topic
	 */
	public function author()
	{
		return $this->belongsTo(User::class);
	}

	/**
	 * a wrapper function for attaching resources to prevent disallowedTopics from being added
	 * @param  Illuminate\Database\Eloquent\Collection $new_resources the resources to be attached
	 * @return void
	 */
	public function attachResources($new_resources)
	{
		foreach ($new_resources as $new_resource)
		{
			$new_resource->attachTopics(Topic::find($this->id));
		}
	}

	public function detachResources($old_resources)
	{
		return $this->resources()->detachResources($old_resources);
	}

	/* This function returns a collection that represents a root */
	public static function getRoot()
	{
		$root = collect([
			"id"=>0, 
			"name"=>"All Topics", 
			"author_id"=>0, 
			"created_at"=>Carbon::now(), 
			"updated_at"=>Carbon::now()
		]);
		return $root;
	} 
}