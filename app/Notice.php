<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Notice extends Model
{
    /**
	 * The attributes that are mass assignable.
	 *
	 * @var array
	 */
    //need to validate parent_id
    protected $fillable = ['description', 'link', 'priority', 'deadline', 'parent_id'];
    
    public $timestamps = false;
    public static function boot() 
    {
        parent::boot();

        static::creating(function ($model) 
        {
            $model->created_at = $model->freshTimestamp();
        });
    }

    /**
	 * returns all classes that have this class as their parent
	 */
    public function children()
	{
		return $this->hasMany(Notice::class, 'parent_id');
    }

    /**
	 * returns all classes for which this class is a child
	 */
	public function parent()
	{
		return $this->belongsTo(Notice::class, 'parent_id');
    }

    /**
	 * define the many-to-one relationship between classes and their author
	 * @return User	the author of this class
	 */
    public function author()
	{
        return $this->belongsTo(User::class)->withDefault(function ($user) {
            //echo 'hello';
            $user->fname = 'Study';
            $user->lname = 'Cloud';
        });
	}
}
