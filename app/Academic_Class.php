<?php

namespace App;

use App\User;
use Carbon\Carbon;
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
		return $this->hasMany(Academic_Class::class, 'parent_id');
	}

	/**
	 * returns all classes for which this class is a child
	 */
	public function parent()
	{
		return $this->belongsTo(Academic_Class::class, 'parent_id');
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

	/* This function returns a collection that represents a root */
	public static function getRoot()
	{
		$root = collect([
			"id"=>0,
			"name"=>"All Classes",
			"author_id"=>0,
			"created_at"=>Carbon::now()->toDateTimeString(),
			"updated_at"=>Carbon::now()->toDateTimeString(),
			"status"=>0
		]);
		return $root;
	}
}
