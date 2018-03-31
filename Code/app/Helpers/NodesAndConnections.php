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
	 * @param  array $nodes the data that needs to be converted
	 * @return Illuminate\Database\Eloquent\Collection        the reformated data as a collection with keys "node" and "connections"
	 */
	public function convertTo($nodes)
	{
		$nodes = collect();
		$connections = collect();

		foreach ($node as $nodes) {
			// check whether the node has been added already before adding it
			if (!$nodes->pluck('id')->contains($node->id))
			{
				// add the node without its pivot
				$nodes->push($node->except(['pivot']));
			}
			// add any connections the node may have
			if (!is_null($node->pivot))
			{
				$connections->push($node->pivot);
			}
		}

		return collect(["nodes" => $nodes->unique(), "connections" => $connections->unique()]);
	}
}