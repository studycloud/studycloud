<?php

namespace App;

use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\Model;

class ResourceContent extends Model
{
	/**
	 * The attributes that are mass assignable.
	 *
	 * @var array
	 */
	protected $fillable = ['name', 'type', 'content'];
	
	/**
	 * define the many-to-one relationship between a resource's contents and the resource itself
	 * @return \Illuminate\Database\Eloquent\Relations\HasMany the relationship accessor
	 */
	public function resource()
	{
		return $this->belongsTo(Resource::class);
	}

	/**
	 * Retrieves the acceptable enum fields for a resource content type
	 * @return array	the available resource types
	 */
	public static function getPossibleTypes() {
		// Pulls column string from DB
		// we use (new static) to get an instance of the current class
		$enumStr = DB::select(DB::raw('SHOW COLUMNS FROM '.(new static)->getTable().' WHERE Field = "type"'))[0]->Type;
		// Parse enum string
		// should look something like:
		// 		enum('text','link','file')
		preg_match_all("/'([^']+)'/", $enumStr, $matches);
		// Return matches
		return isset($matches[1]) ? $matches[1] : [];
	}
}
