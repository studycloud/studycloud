<?php

namespace App\Helpers;

use App\Topic;
use App\Repositories\TopicRepository;

/**
 * A class for using hierarchical data in the nested array format.
 */
class NestedArrays
{
	/**
	 * return the descendants of a topic (and the topic itself) in nested array format
	 * @param  Topic  		$topic the topic whose descendants we want
	 * @return array        an array of arrays of topic IDs
	 */
	public static function topicDescendants(Topic $topic)
	{
		$descendants = (new TopicRepository)->descendants($topic);
		$descendants_as_connections = NodesAndConnections::treeAsConnections($descendants);

		return self::convertToArray($topic->id, $descendants_as_connections, "topic_id", "parent_id");
	}

	/**
	 * return the ancestors of a topic (and the topic itself) in nested array format
	 * @param  Topic  		$topic the topic whose ancestors we want
	 * @return array        an array of arrays of topic IDs
	 */
	public static function topicAncestors(Topic $topic)
	{
		$ancestors = (new TopicRepository)->ancestors($topic);
		$ancestors_as_connections = NodesAndConnections::treeAsConnections($ancestors);

		return self::convertToArray($topic->id, $ancestors_as_connections, "parent_id", "topic_id");
	}

	/**
	 * return a nested array representation of a collection of connections (as collections)
	 * @param  [type] $start               	the item to appear at the root of the nested array tree
	 * @param  Illuminate\Database\Eloquent\Collection $tree_as_connections 	a collection of connections (as collections). you can get this from NodesAndConnections::treeAsConnections()
	 * @param  string $forward             	the key of the key/value pair in the connection whose values should be nested underneath $start
	 * @param  string $backward            	the key of the key/value pair in the connection whose values are the same as $start
	 * @return array						a nested array where the keys of each element have the same type as $start and the values are (nested) arrays
	 */
	private static function convertToArray($start, $tree_as_connections, string $forward, string $backward)
	{
		$next_connections = $tree_as_connections->where($backward, $start);
		$nested_array = [];
		foreach ($next_connections as $connection)
		{
			$nested_array[] = self::convertToArray($connection[$forward], $tree_as_connections, $forward, $backward);
		}
		return [$start => $nested_array];
	}

	/**
	 * convert a nested array to an ascii string
	 * @param array 	$nested_array 	a nested array where the keys of each element are the items in a tree and the values are (nested) arrays
	 * @param int 		$indent 		the number of indentationstp add to the root of the ascii tree
	 * @return string               the ascii representation of this nested array
	 */
	public static function convertToAscii(array $nested_array, $indent = 0)
	{
		$ascii = "";
		$space = "|-- ";
		if ($indent >= 1)
		{
			$space = str_repeat("|   ", $indent-1) . $space;
		}
		else
		{
			$space = "";
		}
		foreach ($nested_array as $key => $nested)
		{
			$ascii .= $space . $key . PHP_EOL;
			$ascii .= self::convertToAscii($nested, $indent+1);
		}
		return $ascii;
	}
}