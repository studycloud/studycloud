<?php

namespace App;

use App\User;
use Illuminate\Database\Eloquent\Model;

class Academic_Class extends Model
{
	/**
	 * The table associated with the model.
	 *
	 * @var string
	 */
	protected $table = 'classes';
	
	/**
	 * The attributes that are mass assignable.
	 *
	 * @var array
	 */
	protected $fillable = ['name'];

	/**
	 * returns all classes that have this class as their parent
	 */
	public function children()
	{
		return $this->belongsToMany(Academic_Class::class, 'class_parent', 'parent_id',  'class_id');
	}

	/**
	 * returns all classes for which this class is a child
	 */
	public function parents()
	{
		return $this->belongsToMany(Academic_Class::class, 'class_parent', 'class_id',  'parent_id');
	}

	/**
	 * returns all resources of this class
	 */
	public function resources()
	{
		return $this->hasMany(Resource::class, 'class_id');
	}

	/**
	 * define the many-to-one relationship between classes and their author
	 * @return User	the author of this class
	 */
	public function author()
	{
		return $this->belongsTo(User::class);
	}
}
