<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class ResourceContent extends Model
{
	/**
	 * The attributes that are mass assignable.
	 *
	 * @var array
	 */
	protected $fillable = ['name', 'resource_id'];
	
	/**
	 * define the many-to-one relationship between a resource's contents and the resource itself
	 * @return \Illuminate\Database\Eloquent\Relations\HasMany the relationship accessor
	 */
	public function resource()
	{
		return $this->belongsTo(Resource::class);
	}
}
