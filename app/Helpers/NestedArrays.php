<?php

namespace App\Helpers;

use App\Topic;
use App\Repositories\TopicRepository;
use App\Academic_Class;
use App\Repositories\ClassRepository;

/**
 * A class for using hierarchical data in the nested array format.
 */
class NestedArrays
{
	/**
	 * return the descendants of a topic (and the topic itself) in nested array format
	 * @param  Topic		$topic the topic whose descendants we want
	 * @return array		an array of arrays of topic IDs
	 */
	public static function topicDescendants(Topic $topic)
	{
		$descendants = (new TopicRepository)->descendants($topic);
		$descendants_as_connections = NodesAndConnections::treeAsConnections($descendants);

		return [$topic->id => self::convertToArray($topic->id, $descendants_as_connections, "topic_id", "parent_id")];
	}

	/**
	 * return the descendants of a class (and the class itself) in nested array format
	 * @param  Class		$class the class whose descendants we want
	 * @return array		an array of arrays of class IDs
	 */
	public static function classDescendants(Academic_Class $class)
	{
		$descendants = (new ClassRepository)->descendants($class);
		$descendants_as_connections = NodesAndConnections::treeAsConnections($descendants);

		return [$class->id => self::convertToArray($class->id, $descendants_as_connections, "class_id", "parent_id")];
	}	

	/**
	 * return the ancestors of a topic (and the topic itself) in nested array format
	 * @param  Topic		$topic the topic whose ancestors we want
	 * @return array		an array of arrays of topic IDs
	 */
	public static function topicAncestors(Topic $topic)
	{
		$ancestors = (new TopicRepository)->ancestors($topic);
		$ancestors_as_connections = NodesAndConnections::treeAsConnections($ancestors);

		return [$topic->id => self::convertToArray($topic->id, $ancestors_as_connections, "parent_id", "topic_id")];
	}

	/**
	 * return the ancestors of a class (and the class itself) in nested array format
	 * @param  Class		$class the topic whose ancestors we want
	 * @return array		an array of arrays of topic IDs
	 */
	public static function classAncestors(Academic_Class $class)
	{
		$ancestors = (new ClassRepository)->ancestors($class);
		$ancestors_as_connections = NodesAndConnections::treeAsConnections($ancestors);

		return [$class->id => self::convertToArray($class->id, $ancestors_as_connections, "parent_id", "class_id")];
	}

	/**
	 * return a nested array representation of a collection of connections (as collections)
	 * @param  int	 	$start 		the item to appear at the root of the nested array tree
	 * @param  Illuminate\Database\Eloquent\Collection $tree_as_connections 	a collection of connections (as collections). you can get this from NodesAndConnections::treeAsConnections()
	 * @param  string	$forward 	the key of the key/value pair in the connection whose values should be nested underneath $start (ex: "parent_id" or "topic_id")
	 * @param  string	$backward 	the key of the key/value pair in the connection whose values are the same as $start (ex: "parent_id" or "topic_id")
	 * @return array						a nested array where the keys of each element have the same type as $start and the values are (nested) arrays
	 */
	private static function convertToArray($start, $tree_as_connections, string $forward, string $backward)
	{
		// get all connections where $backward is the same as $start
		$next_connections = $tree_as_connections->where($backward, $start);
		$nested_array = [];
		// iterate through all of the relevant connections and add them to nested array
		foreach ($next_connections as $connection)
		{
			// make recursive calls to get the "parents"/"children" of the "parents"/"children" in each $connection
			$nested_array[ $connection[$forward] ] = self::convertToArray($connection[$forward], $tree_as_connections, $forward, $backward);
		}
		return $nested_array;
	}

	/**
	 * convert a nested array to an ascii string
	 * @param array 	$nested_array 	a nested array where the keys of each element are the items in a tree and the values are (nested) arrays
	 * @param int 		$indent 		the number of indentationstp add to the root of the ascii tree
	 * @return string					the ascii representation of this nested array
	 */
	public static function convertToAscii(array $nested_array, $indent = 0)
	{
		$ascii = "";
		// delimiter for a nested connection
		$space = "|-- ";
		// only create spacing and indentation for nested items
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
			// recursively create ascii for further nestings
			$ascii .= self::convertToAscii($nested, $indent+1);
		}
		return $ascii;
	}
}