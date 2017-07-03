<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class TopicParent extends Model
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'topic_parent';

    /**
     * Indicates if the model should be timestamped.
     *
     * @var bool
     */
    public $timestamps = false;

    /**
     * Makes an array representing the topic tree.
     *
     * @return array $tree
     */
    // public static function generateArrayTree($topics = [])
    // {
    //     $tree = [];
    //     if ($topics = [])
    //     {
    //         $topics = Topic::getTopLevelTopics()->pluck('id')->all();
    //     }
    //     foreach ($topics as $topic) {
    //         $children = $topic->children()->pluck('id')->all();
    //         $tree[$topic->id] = self::generateArrayTree($children);
    //     }
    //     return $tree;
    // }
}
