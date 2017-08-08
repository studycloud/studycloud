<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class ResourceTopic extends Model
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'resource_topic';

    /**
     * Indicates if the model should be timestamped.
     *
     * @var bool
     */
    public $timestamps = false;
}
