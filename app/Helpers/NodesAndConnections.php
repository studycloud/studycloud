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
			// check whether the node has been added already before adding it
			if (!$nodes->pluck('id')->contains($node['id']))
			{
				// add the node without its pivot
				$nodes->push($node->except(['pivot']));
			}
			// add any connections the node may have
			if ($node->has('pivot'))
			{
				$connections->push(collect($node['pivot']));
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
}