<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Meta extends Model
{
	/**
	 * The table associated with the model.
	 *
	 * @var string
	 */
	protected $table = 'meta';
	
	/**
	 * The attributes that are mass assignable.
	 *
	 * @var array
	 */
	protected $fillable = ['key', 'value'];

	/**
	 * Override the find method
	 * If not found, defaults to old find
	 */
	public static function find($key)
	{
		$obj = self::where('key', $key)->limit(1);

		if ($obj->count() == 0) {
			return static::query()->find($key);
		}

		$obj = $obj->first();

		return $obj;
	}

	/**
	 * Create a new 'add' method with a simplified interface
	 * Adds if not already present and updates if it is
	 */
	public static function add($key, $value)
	{
		return self::updateOrCreate(['key' => $key], ['value' => $value]);
	}
}
