<?php

namespace App\Helpers;

/**
 * A class for converting data with relationships to the nodes/connections format.
 */
class NodesAndConnections
{
	/**
	 * convert data to the nodes/connections format
	 * Note: your nodes must have a pivot attribute
	 * @param  array $old_nodes the data that needs to be converted, in the "nodes with pivots" format
	 * @return Illuminate\Database\Eloquent\Collection        the reformated data as a collection with keys "node" and "connections"
	 */
	public static function convertTo($old_nodes)
	{
		$nodes = collect();
		$connections = collect();

		foreach ($old_nodes as $node) {
			// add any connections the node may have
			if ($node->has('pivot'))
			{
				$connections->push(collect($node['pivot']));
			}
			// check whether the node has been added already before adding it
			if (!$nodes->pluck('id')->contains($node['id']))
			{
				// remove the pivot if it exists
				if ($node->has('pivot'))
				{
					$node = $node->except(['pivot']);
				}
				// add the node without its pivot
				$nodes->push($node);
			}
		}

		return collect(["nodes" => $nodes, "connections" => $connections->unique()]);
	}

	/**
	 * convert "nodes with pivots" data to a tree represented only by connections
	 * @param  array $old_nodes the data that needs to be converted, in the "nodes with pivots" format
	 * @return Illuminate\Database\Eloquent\Collection            the reformated data as a collection of connection collections
	 */
	public static function treeAsConnections($old_nodes)
	{
		return self::convertTo($old_nodes)["connections"];
	}

	/**
	 * some data won't have pivot objects, but conversion function depends on their existence
	 * let's format an $node into the "nodes with pivot" format
	 * @param array		$node	the data that needs to be converted; the node must have a 'parent_id' attribute
	 * @param string	$name		the name of the type of object (ex: "topic" or "class")
	 * @param int|null	$other_id	the id of the pivot's parent_id, if not the parent_id itself
	 * @return array				the reformated data as a collection with a pivot attribute
	 * added and the 'parent_id' attribute removed
	 */
	public static function addPivot($node, $name, $replace_child=null)
	{
		// add the pivot attribute if there's a parent
		if (!is_null($replace_child))
		{
			$node['pivot'] = [
				'parent_id' => $node['id'],
				$name.'_id' => $replace_child
			];
		}
		elseif (!is_null($node['parent_id']))
		{
			$node['pivot'] = [
				'parent_id' => $node['parent_id'],
				$name.'_id' => $node['id']
			];
		}
		// remove the parent_id attribute
		unset($node['parent_id']);
		return $node;
	}
}