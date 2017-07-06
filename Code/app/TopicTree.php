<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class TopicTree extends Topic
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'topics';
    
    /**
     * The accessors to append to the model's array form.
     *
     * @var array
     */
	protected $appends = ['children'];

	/**
	 * @return \Illuminate\Database\Eloquent\Collection
	 */
    public function getChildrenAttribute()
    {
    	return $this->children()->get();
    }

	/**
	 * returns all topics that have this topic as their parent
	 */
	public function children()
	{
		return $this->belongsToMany(TopicTree::class, 'topic_parent', 'parent_id',  'topic_id');
	}
}
