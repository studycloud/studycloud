<?php

namespace App\Http\Controllers;

use App\Topic;
use App\TopicParent;
use App\Resource;
// use Barryvdh\Debugbar\Facade as Debugbar;
use Illuminate\Http\Request;

class TopicTreeController extends Controller
{
    protected $nodes;

    protected $connections;


    /**
     * a wrapper for show() that parses the query string. This
     * function is automatically invoked by Laravel when the 
     * controller is called.
     * @return \Illuminate\Database\Eloquent\Collection            the return value of show()
     */
    public function __invoke(Request $request)
    {
        $topic_id = $request->query('topic');
        if ($topic_id == "")
        {
            $topic_id = null;
        }
        $levels = $request->query('levels');
        if ($levels == "")
        {
            $levels = null;
        }
        return $this->show($topic_id, $levels);
    }

    /**
     * converts a portion of the tree to JSON for traversal by the JavaScript team
     * @param  integer $topic_id the id of the current topic in the tree; defaults to the root of the tree, which has an id of 0
     * @param  int     $levels   the number of levels of the tree to return; defaults to infinity
     * @return \Illuminate\Database\Eloquent\Collection            the nodes and connections of the target portion of the tree
     */
    public function show($topic_id = 0, $levels = null)
    {
        // initialize our data members to empty collections
        $this->nodes = collect();
        $this->connections = collect();

        // get the current topic the tree is pointing to
        // if the topic_id is 0, the user wants the root of the tree
        $topic = null;
        if ($topic_id != 0)
        {
            $topic = Topic::find($topic_id);
        }
        $this->addDescendants($topic, $levels);

        // return a collection of the resulting lists of nodes and connections
        return collect(["nodes" => $this->nodes->unique(), "connections" => $this->connections]);
    }

    /**
     * get the descendants of a topic in a flat collection
     * @param  integer $topic_id the id of the current topic in the tree; defaults to the root of the tree, which has an id of 0
     * @param  int $levels the number of levels of descendants to get; returns all if $levels is not specified or is less than 0
     */
    private function addDescendants($topic = null, $levels = null)
    {
        // base case: $levels == 0
        if ($levels != 0 || is_null($levels))
        {
            if (is_null($topic))
            {
                $children = Topic::getTopLevelTopics();
            }
            else
            {
                $children = $topic->children()->get();
            }
            foreach ($children as $child) {
                // this is a memoization check
                // it prevents us from calling addDescendants on any topic more than once
                if (!$this->nodes->pluck('target')->contains($child->target))
                {
                    $this->add($child);
                    // RECURSION!
                    $this->addDescendants($child, $levels - 1);
                }
            }
        }
    }

    /**
     * adds the given node and any connections to the appropriate $nodes and $connections collections
     * @param \Illuminate\Database\Eloquent\Collection $nodes the nodes to add
     */
    private function add($node)
    {
        $this->nodes->push(
            $this->processNode($node)
        );

        if (!is_null($node->pivot))
        {
            $this->connections->push(
                $this->processPivot($node->pivot)
            );
        }

        // if this node is a topic, we'll also have to add it's resources to the list of nodes
        if (is_a($node, "App\Topic"))
        {
            $resources = $node->resources()->get();
            foreach ($resources as $resource) {
                $this->add($resource);
            }
            //recursion sucks - amp
        }
    }

    /**
     * process this node so that it shows the correct 
     * attributes when the request is returned to the user
     * @param  \Illuminate\Database\Eloquent\Collection $node the nodes to process
     * @return \Illuminate\Database\Eloquent\Collection       the processed nodes
     */
    private function processNode($node)
    {
        return $node->makeHidden('pivot')->makeVisible('target')->makeHidden('id');
    }

    /**
     * process this connection so that it shows the correct 
     * attributes when the request is returned to the user
     * @param  Illuminate\Database\Eloquent\Relations\Pivot $pivot the pivot to process
     * @return \Illuminate\Database\Eloquent\Collection        the processed pivot as a collection
     */
    private function processPivot($pivot)
    {
        // if the pivot object has a parent_id, we know that it's a connection
        // between a topic and its parent
        if ($pivot->parent_id)
        {
            $source = "t".($pivot->parent_id);
            $target = "t".($pivot->topic_id);
        }
        // otherwise, we know that it's a connection between a
        // resource and its topic
        elseif ($pivot->resource_id)
        {
            $source = "t".($pivot->topic_id);
            $target = "r".($pivot->resource_id);
        }

        return collect(['source' => $source, 'target' => $target]);
    }
}
