<?php

namespace App\Repositories;

use App\Academic_Class;
use App\Helpers\NestedArrays;
use App\Helpers\NodesAndConnections;

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
	 * @param  int 			$class_id           	the ancestor class
	 * @param  int  		$descendant_class_id   	the descendant to search for
	 * @param  Collection 	$connections 		a portion of the tree to traverse, as a collection of connections
	 * @return boolean                  			whether $descendant_class_id is an descendant of $class
	 */
	public static function isDescendant($class_id, $descendant_class_id, $connections)
	{
		// base case: descendant_class is an descendant of class if they are the same
		if ($class_id == $descendant_class_id)
		{
			return true;
		}
		// get the class collections in $connections with parent_ids equal to $class_id
		$classes = $connections->where('parent_id', $class_id);
		$isDescendant = false;
		// call isDescendant() with each of the classes
		// and then OR all of the results together to get a final value
		foreach ($classes as $class)
		{
			// is the parent of this $class a descendant of $descendant_class_id?
			$isDescendant = $isDescendant || self::isDescendant($class['class_id'], $descendant_class_id, $connections);
		}
		return $isDescendant;
	}

	/**
	 * given a portion of the tree, check to see whether $ancestor_class_id is an ancestor of $class_id
	 * @param  int 			$class_id 			the descendant class
	 * @param  int 			$ancestor_class_id 	the ancestor to search for
	 * @param  Collection  	$connections 	a portion of the tree to traverse, as a collection of connections
	 * @return boolean                  		whether $ancestor_class_id is an ancestor of $class
	 */
	public static function isAncestor($class_id, $ancestor_class_id, $connections)
	{
		// base case: ancestor_class is an ancestor of class if they are the same
		while ($class_id != $ancestor_class_id)
		{
			// get the connections in $connections with class_ids equal to $class_id
			$classes = $connections->where('class_id', $class_id);
			// check that the number of classes is 1 before attempting to continue searching the tree
			if ($classes->count() > 1)
			{
				throw new Exception('The class with ID '.$class_id.' has multiple parents.');
			}
			elseif ($classes->count() < 1)
			{			
				return false;
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

	/**
	 * check whether we can change the parent and children of a class all at once
	 * @param	int|null			$class		the class id for the class we want to attach things to or null for the root
	 * @param	int|null			$parent		the id of the new parent class or null if we shouldn't replace the current one
	 * @param	array 				$children 	an array of class id's that should be attached as children to this class
	 * @param	Collection|null		$tree		all parents and children of $class in the connections format; if null, queries will be performed to retrieve the required data
	 * @param	string|null						if the parent and children cannot be added, returns a message explaining why; otherwise, returns null
	 */
	public static function validateClassAttach($class=null, $parent=null, $children=[], $tree=null)
	{
		// if we're dealing with the root class
		if (is_null($class))
		{
			// the root class cannot have a parent
			if (!is_null($parent))
			{
				return "The root class cannot be assigned a parent";
			}
			// you can attach any children to the root
			return null;
		}
		// get the descendants that are required
		if (is_null($tree))
		{
			$tree = NodesAndConnections::treeAsConnections(
				(new ClassRepository)->descendants($class)
			);
		}
		// return failure if the new parent is a descendant of $class
		// make sure to include the new children when you check
		if (self::isDescendant($class, $parent, $tree))
		{
			return "New parent class ".$parent." is a descendent. It cannot be added as a parent.";
		}
		// get the ancestors that are required
		if (is_null($tree))
		{
			// check if we need to retrieve the new ancestors
			if (!is_null($parent))
			{
				// get the ancestors of the new parent
				$tree = NodesAndConnections::treeAsConnections(
					(new ClassRepository)->ancestors($parent)
				);
				// add the connection between the current parent and the new parent
				$tree->prepend(
					collect([
						'parent_id' => $parent,
						'class_id' => $class
					])
				);
			}
			else	// retrieve the current ancestors
			{
				$tree = NodesAndConnections::treeAsConnections(
					(new ClassRepository)->ancestors($class)
				);
			}
		}
		// return failure if the children we want to add are ancestors of $class
		foreach ($children as $child)
		{
			if (self::isAncestor($class, $child, $tree))
			{
				// this will stop on first failure but maybe we want it to find all bad children?
				if (is_null($parent))
				{
					return "New child class ".$child." is an ancestor. It cannot be added as a child.";
				}
				else
				{
					return "New child class ".$child." will be an ancestor. It cannot be added as a child.";
				}
			}
		}
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