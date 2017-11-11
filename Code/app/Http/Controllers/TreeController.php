<?php

namespace App\Http\Controllers;

use App\Topic;
use App\TopicParent;
use App\Resource;
// use Barryvdh\Debugbar\Facade as Debugbar;
use Illuminate\Http\Request;

class TreeController extends Controller
{
    protected $nodes;

    protected $connections;


    /**
     * converts a portion of the tree to JSON for traversal by the JavaScript team
     * @param  integer $topic_id the id of the current topic in the tree; defaults to the root of the tree, which has an id of 0
     * @param  int     $levels   the number of levels of the tree to return; defaults to infinity
     * @return \Illuminate\Database\Eloquent\Collection            the nodes and connections of the target portion of the tree
     */
    public function toJson($topic_id = 0, $levels = null)
    {
        // initialize our data members to empty collections
        $this->nodes = collect();
        $this->connections = collect();

        // if the topic_id is 0, the user wants the root of the tree
        // note that $levels can't be 0 because then descendants() won't do the right things
        if ($topic_id == 0 && ($levels != 0 || is_null($levels)))
        {
            // get the nodes right underneath the root
            $nodes = Topic::getTopLevelTopics();
            // add them to the collection of nodes
            $this->addNodes($nodes);
            // add their children to the list of nodes if $levels > 1
            foreach ($nodes as $node) {
                $this->addNodes($node->descendants($levels - 1));
            }
        }
        // otherwise, we want the children of the current topic
        elseif ($topic_id != 0)
        {
            // get the current topic the tree is pointing to
            $topic = Topic::find($topic_id);
            $nodes = $topic->descendants($levels);
            // add the descendants of the current topic to the list of nodes
            $this->addNodes($nodes);
        }
        // return a collection of the resulting lists of nodes and connections
        return collect(["nodes" => $this->nodes->unique(), "connections" => $this->connections]);
    }

    /**
     * adds the given nodes and any connections to the appropriate $nodes and $connections collections
     * @param \Illuminate\Database\Eloquent\Collection $nodes the nodes to add
     */
    private function addNodes($nodes)
    {
        // add each node and its connections to the $nodes and $connections data members
        foreach ($nodes as $node)
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
                $this->addNodes($node->resources()->get());
                //recursion sucks - amp
            }
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
        return $node->makeHidden('pivot')->makeVisible('node_id')->makeHidden('id');
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
            $parent_id = "t".($pivot->parent_id);
            $node_id = "t".($pivot->topic_id);
        }
        // otherwise, we know that it's a connection between a
        // resource and its topic
        elseif ($pivot->resource_id)
        {
            $parent_id = "t".($pivot->topic_id);
            $node_id = "r".($pivot->resource_id);
        }

        return collect(['parent_id' => $parent_id, 'node_id' => $node_id]);
    }
}
