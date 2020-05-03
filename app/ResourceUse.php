<?php

namespace App;

use App\Resource;
use Illuminate\Database\Eloquent\Model;

class ResourceUse extends Model
{
	/**
	 * Indicates if the model should be timestamped.
	 *
	 * @var bool
	 */
	public $timestamps = false;

	/**
	 * The attributes that are mass assignable.
	 *
	 * @var array
	 */
	protected $fillable = ['name', 'author_id'];

	/**
	 * define the many-to-one relationship between resources and their use
	 * @return ResourceUse	this resource's use
	 */
	public function resources()
	{
		return $this->hasMany(Resource::class, 'use_id');
	}
}
