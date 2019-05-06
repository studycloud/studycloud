<?php

namespace App\Repositories;

use App\Academic_Class;
use App\Helpers\NestedArrays;
use App\Helpers\NodesAndConnections;
use Illuminate\Database\Eloquent\Collection;

class ClassRepository 
{
	protected $memoize = [];
	
	/**
	 * get the descendants of a class in a flat collection
	 * @param  App\Academic_Class the current class in the tree; defaults to the root of the tree
	 * @param  int $levels the number of levels of descendants to get; returns all if $levels is not specified or is less than 0
	 * @return Illuminate\Database\Eloquent\Collection
	 */
	public function descendants(Academic_Class $class = null, int $levels = null, $root = null)
	{
		$tree = collect();

		// base case: $levels == 0
		// also do a memoization check to prevent us from
		// executing a query for a class that we've already found
		if (
			($levels != 0 || is_null($levels)) &&
			(is_null($class) || !in_array($class->id, $this->memoize))
		) 
		{
			if (is_null($class))
			{
				$children = self::getTopLevelClasses();

				if (!is_null($root))
				{
					foreach ($children as $child) {  //iterates through each top level class
						// add a pivot element to each class
						$child->pivot = collect(["parent_id" => $root['id'], "class_id" => $child["id"]]);
					}
				}
			}
			else
			{
				$children = $class->children()->get();
				// add the class id to the list of classes that have already been called
				array_push($this->memoize, $class->id);
			}

			foreach ($children as $child) {
				// add the child to the tree
				// but add a pivot object to it first
				$tree->push(
					NodesAndConnections::addPivot(collect($child), "class")
				);
				$tree = $tree->merge(
					// RECURSION!
					$this->descendants($child, $levels - 1, $root)
				);
			}
		}
		return $tree;
	}

	/**
	 * get the ancestors of a class in a flat collection
	 * @param  App\Academic_Class the current class in the tree; defaults to the root of the tree
	 * @param  int $levels the number of levels of ancestors to get; returns all if $levels is not specified or is less than 0
	 * @return Illuminate\Database\Eloquent\Collection
	 */
	public function ancestors(Academic_Class $class = null, int $levels = null, $root = null)
	{
		$tree = collect();

		// base case: $levels == 0
		// also do a memoization check to prevent us from
		// executing a query for a class that we've already found
		if (
			($levels != 0 || is_null($levels)) &&
			(is_null($class) || !in_array($class->id, $this->memoize))
		)
		{
			if (is_null($class))
			{
				$parents = collect();
			}
			else
			{
				$parents = $class->parent()->get();
				// add the class id to the list of classes that have already been called
				array_push($this->memoize, $class->id);
				// add the root and its connection to this class,
				// if this class doesn't have any parents
				if ($parents->isEmpty() && !is_null($root))
				{
					$root->put("pivot", collect(["parent_id" => $root['id'], "class_id" => $class['id']]));
					$tree->push($root);
				}
			}
			foreach ($parents as $parent) {
				// add the parent to the tree
				$tree->push(
					NodesAndConnections::addPivot(collect($parent), "class", $class->id)
				);
				$tree = $tree->merge(
					// RECURSION!
					$this->ancestors($parent, $levels - 1, $root)
				);
			}
		}
		return $tree;
	}

	public static function getTopLevelClasses()
	{
		return Academic_Class::whereNull('parent_id')->get();
	}

	public static function getLeafClasses()
	{
		return Academic_Class::whereNotIn('id', function ($query)
			{
				$query->select('parent_id')->distinct()->from('classes')->whereNotNull('parent_id');
			}
		)->get();
	}

	/**
	 * given a portion of the tree, check to see whether $descendant_class_id is a descendant of $class_id
	 * @param  int 			$class_id				the ancestor class
	 * @param  int|array	$descendant_class_id	the descendant to search for
	 * @param  Collection	$connections			a portion of the tree to traverse, in the connections or separated connections format
	 * @return  boolean|array						whether $descendant_class_id is an descendant of $class; if $descendant_class_id is an array, return the elements in it that are descendants
	 */
	public static function isDescendant($class_id, $descendant_class_id, $connections)
	{
		if (!$connections->has('descendants'))
		{
			if (is_array($descendant_class_id))
			{
				$descendants = [];
				$descendant_idx = array_search($class_id, $descendant_class_id, $strict=true);
				// base case: descendant_class is an descendant of class if class is contained within it
				if ($descendant_idx !== false)
				{
					$descendants = [$class_id];
					unset($descendant_class_id[$descendant_idx]);
				}
				// get the class collections in $connections with parent_ids equal to $class_id
				$classes = $connections->where('parent_id', $class_id);
				// call isDescendant() with each of the classes
				// and then merge all of the results together to get a final value
				return $classes->map(
					function ($class) use ($descendant_class_id, $connections)
					{
						return self::isDescendant($class['class_id'], $descendant_class_id, $connections);
					}
				)->flatten()->merge($descendants)->toArray();
			}
			else
			{
				// base case: descendant_class is an descendant of class if they are the same
				if ($class_id == $descendant_class_id)
				{
					return true;
				}
				$isDescendant = false;
				// get the class collections in $connections with parent_ids equal to $class_id
				$classes = $connections->where('parent_id', $class_id);
				// call isDescendant() with each of the classes
				// and then OR all of the results together to get a final value
				foreach ($classes as $class)
				{
					// is the parent of this $class a descendant of $descendant_class_id?
					$isDescendant = $isDescendant || self::isDescendant($class['class_id'], $descendant_class_id, $connections);
				}
				return $isDescendant;
			}
		}
		else
		{
			// perform a simple intersection operation to find the elements in $descendant_class_id that are class_id's in $connections
			$descendants = $connections['descendants']->pluck('class_id')->push($class_id)->intersect($descendant_class_id)->values()->toArray();
			// if the input was an array, return the descendants
			// otherwise, return whether we found the descendant
			return is_array($descendant_class_id) ? $descendants : count($descendants) == 1;
		}
	}

	/**
	 * given a portion of the tree, check to see whether $ancestor_class_id is an ancestor of $class_id
	 * @param  int			$class_id			the descendant class
	 * @param  int|array	$ancestor_class_id	the ancestor to search for
	 * @param  Collection	$connections		a portion of the tree to traverse, in the connections or separated connections format
	 * @return boolean|array					whether $ancestor_class_id is an ancestor of $class; if $ancestor_class_id is an array, return the elements in it that are ancestors
	 */
	public static function isAncestor($class_id, $ancestor_class_id, $connections)
	{
		if (!$connections->has('ancestors'))
		{
			$ancestors = [];
			// base case: ancestor_class is an ancestor of class if they are the same
			while ($class_id !== $ancestor_class_id)
			{
				if (is_array($ancestor_class_id))
				{
					$ancestor_idx = array_search($class_id, $ancestor_class_id, $strict=true);
					// base case: ancestor_class is an ancestor of class if class is contained within it
					if ($ancestor_idx !== false)
					{
						$ancestors[] = $class_id;
						unset($ancestor_class_id[$ancestor_idx]);
					}
				}
				// get the connections in $connections with class_ids equal to $class_id
				$classes = $connections->where('class_id', $class_id);
				// check that the number of classes is 1 before attempting to continue searching the tree
				if ($classes->count() > 1)
				{
					throw new Exception('The class with ID '.$class_id.' has multiple parents.');
				}
				elseif ($classes->count() < 1)
				{
					// note: we are gauranteed to reach this point if $ancestor_class_id is an array b/c the while condition will never be true
					// check if $ancestor_class_id is an array and if it is, return any ancestors we've found so far
					return is_array($ancestor_class_id) ? $ancestors : false;
				}
				else
				{
					// set the current parent to be the new descendant class
					// ie move up the tree
					$class_id = $classes->first()['parent_id'];
				}
			}
			// if we get here, it means that we exited the while loop
			// because we found the ancestor
			return true;
		}
		else
		{
			// perform a simple intersection operation to find the elements in $ancestor_class_id that are parent_id's in $connections
			$ancestors = $connections['ancestors']->pluck('parent_id')->push($class_id)->intersect($ancestor_class_id)->values()->toArray();
			// if the input was an array, return the ancestors
			// otherwise, return whether we found the ancestor
			return is_array($ancestor_class_id) ? $ancestors : count($ancestors) == 1;
		}
	}

	/**
	 * retrieve the depths of each class in $classes
	 * @param  Collection	$classes	the classes for which we want depths, as a Collection of Models
	 * @return array		$depths		an array of depths where the keys are the IDs of the classes and the values are the depths
	 */
	public static function depths(Collection $classes, $start_depth=0)
	{
		// get a collection where the keys are the class IDs and the values are their parent_id
		$classes = $classes->sortBy('parent_id')->pluck('parent_id', 'id')->toArray();
		$depths = [];

		// big picture: pick classes that have the lowest parent_id and then iterate down the tree
		// keep doing that until we run out of classes
		while (!empty($classes))
		{
			// get class with the lowest parent_id
			$parent = reset($classes);
			// get depths for all clases under $parent
			$depth = self::depth($classes, $parent, $start_depth);
			// remove classes that we already have depths for
			$classes = array_diff_key($classes, $depth);
			// append depths to depths we currently have
			$depths = $depths + $depth;
		}
		return $depths;
	}

	/**
	 * get the depths of the classes in $classes under $root, given that it has depth $depth
	 * @param  array	$classes	an array with class IDs as the keys and their parent_ids as the values
	 * @param  int		$root		the class ID of the root
	 * @param  int		$depth		the depth of the root
	 * @return array	$depths		an array with class IDs as the keys and their depths as the values
	 */
	private static function depth($classes, $root, $depth=0)
	{
		// get the classes with root as their parent
		$curr_classes = array_keys($classes, $root, true);
		// get an array with $curr_classes as the keys and $depth as the child
		$depths = array_fill_keys($curr_classes, $depth);
		// get the depths of the classes underneath each of the classes in $curr_classes
		foreach ($curr_classes as $new_root)
		{
			$children = self::depth($classes, $new_root, $depth+1);
			$depths = $depths + $children;
		}
		return $depths;
	}
	
	public static function printAsciiDescendants($class)
	{
		if (is_int($class))
		{
			$class = Academic_Class::find($class);
		}

		echo NestedArrays::convertToAscii(NestedArrays::classDescendants($class));
	}
	
	public static function printAsciiAncestors($class)
	{
		if (is_int($class))
		{
			$class = Academic_Class::find($class);
		}

		echo NestedArrays::convertToAscii(NestedArrays::classAncestors($class));
	}
	
	public static function asciiTree($class)
	{
		if (is_int($class))
		{
			$class = Academic_Class::find($class);
		}

		echo "DESCENDANTS\n";
		self::printAsciiDescendants($class);
		echo "\nANCESTORS\n";
		self::printAsciiAncestors($class);
	}
	
}