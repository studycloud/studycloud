<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Topic extends Model
{
	/**
	 * The attributes that are mass assignable.
	 *
	 * @var array
	 */
	protected $fillable = ['name', 'author_id'];

	protected $appends = ['unique_id'];

    protected $hidden = ['unique_id'];
 
 	public function getUniqueIdAttribute()
 	{
 		return "t".($this->attributes['id']);
 	}

	/**
	 * Returns the updated tree.
	 *
	 * @return \Illuminate\Database\Eloquent\Collection $tree
	 */
	public static function getTree()
	{
		return self::getTopLevelTopics();
	}

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

	/**
	 * Finds the topics that are at the root (very top) of the tree.
	 *
	 * @return Illuminate\Database\Eloquent\Collection
	 */
	public static function getTopLevelTopics()
	{
		return self::whereNotIn('id', TopicParent::pluck('topic_id')->all())->get();
	}

	private function resources()
	{
		return $this->belongsToMany(Resource::class, 'resource_topic', 'topic_id', 'resource_id');
	}

	public function getResources()
	{
		return $this->resources()->get();
	}

	/**
	 * a wrapper function for the attaching resources to prevent disallowedTopics from being added
	 * @param  Illuminate\Database\Eloquent\Collection $new_resources the resources to be attached
	 * @return void
	 */
	public function attachResources($new_resources)
	{
		foreach ($new_resources as $new_resource)
		{
			// print_r(collect([Topic::find($this->id)]));
			$new_resource->attachTopics(Topic::find($this->id));
		}
	}

	public function detachResources($old_resources)
	{
		return $this->resources()->detachResources($old_resources);
	}

	/**
	 * get the parents of each parent of each parent (etc) of this topic in a flat collection
	 * @return Illuminate\Database\Eloquent\Collection
	 */
	public function ancestors()
	{
		$parents = $this->parents()->get();
		$ancestors = $parents;
		foreach ($parents as $parent) {
			$ancestors = $ancestors->merge($parent->ancestors());
		}
		return $ancestors;
	}

	/**
	 * get the descendants of this topic in a flat collection
	 * @param  int $levels the number of levels of descendants to get; returns all if $levels is not specified or is less than 0
	 * @return Illuminate\Database\Eloquent\Collection
	 */
	public function descendants($levels = null)
	{
		$children = $this->children()->get();

		if (!is_null($levels) && $levels == 0)
		{
			return collect();
		}
		elseif ($levels == 1)
		{
			return $children;
		}
		else
		{
			$descendants = $children;
			// iterate through each child and find its descendants
			foreach ($children as $child) {
				// add the subsequent descendants to the flat collection. RECURSION!
				// make sure to increment the $levels
				$descendants = $descendants->merge($child->descendants($levels - 1)); // note that if $levels is null, $levels - 1 = -1
			}
			// we make sure to call unique, in case there are duplicates
			return $descendants->unique();
		}
	}
}