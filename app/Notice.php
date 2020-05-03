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
    //status is null if not claimed, and owner_id if claimed
    protected $fillable = ['description', 'link', 'priority', 'deadline', 'parent_id', 'status'];
    
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
	 * define the many-to-one relationship between notices and their author
	 * @return User	the author of this class, or "Study Cloud" if null
	 */
    public function author()
	{
        return $this->belongsTo(User::class)->withDefault(function ($user) {
            //echo 'hello';
            $user->fname = 'Study';
            $user->lname = 'Cloud';
        });
    }
    
    /**
     * defines the many-to-one relationship between notices and their owner
     * @return User the owner of the notice, or "No One" if null
     */
    public function owner()
    {
        return $this->belongsTo(User::class, 'status')->withDefault(function ($user) {
            $user->fname = "No";
            $user->lname = "One";
        });
        // $owner = User::find($this->status);
        // if ($owner)
        // {
        //     return $owner->name();
        // }
        // return "No One";
    }
}
