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
	 * @param  array $old_nodes the data that needs to be converted
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
			if (!is_null($node['pivot']))
			{
				$connections->push(collect($node['pivot']));
			}
		}

		return collect(["nodes" => $nodes, "connections" => $connections]);
	}
}