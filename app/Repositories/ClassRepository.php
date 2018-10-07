<?php

namespace App\Repositories;

use App\Academic_Class;
use App\Helpers\NestedArrays;

class ClassRepository 
{
	protected $memoize = [];
	
	/**
	 * get the descendants of a class in a flat collection
	 * @param  App\Academic_Class the current class in the tree; defaults to the root of the tree
	 * @param  int $levels the number of levels of descendants to get; returns all if $levels is not specified or is less than 0
	 * @return Illuminate\Database\Eloquent\Collection
	 */
	public function descendants(Academic_Class $class = null, int $levels = null)
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
			if (is_null($class)) #if the class is null, how would we be able
			#to get topLevelClasses? -> wouldn't they not exist
			{
				$children = self::getTopLevelClasses();
			}
			else
			{
				$children = $class->children()->get();
				// add the class id to the list of classes that have already been called
				array_push($this->memoize, $class->id);
			}
			foreach ($children as $child) {
				// add the child to the tree
				$tree->push(collect($child));
				$tree = $tree->merge(
					// RECURSION!
					$this->descendants($child, $levels - 1)
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
	public function ancestors(Academic_Class $class = null, int $levels = null)
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
				$parents = $class->parents()->get();
				// add the class id to the list of classes that have already been called
				array_push($this->memoize, $class->id);
			}
			foreach ($parents as $parent) {
				// add the parent to the tree
				$tree->push(collect($parent));
				$tree = $tree->merge(
					// RECURSION!
					$this->ancestors($parent, $levels - 1)
				);
			}
		}
		return $tree;
	}

	public static function getTopLevelClasses()
	{
		return Academic_Class::whereNotExists(function ($query)
			{
				$query->select('class_id')->distinct()->from('class_parent')->whereRaw('class_parent.class_id = classes.id');
			}
		)->get();
	}

	public static function getLeafClasses()
	{
		return Academic_Class::whereNotExists(function ($query)
			{
				$query->select('class_id')->distinct()->from('class_parent')->whereRaw('class_parent.parent_id = classes.id');
			}
		)->get();
	}

	public static function isDescendant($class_id, $descendant_class_id, $disallowed_classes)
	{
		// base case: descendant_class is an descendant of class if they are the same
		if ($class_id == $descendant_class_id)
		{
			return true;
		}
		// get the class collections in $disallowed_classes with parent_ids equal to $class_id
		$classes = $disallowed_classes->where('parent_id', $class_id);
		$isDescendant = false;
		// call isDescendant() with each of the classes
		// and then OR all of the results together to get a final value
		foreach ($classes as $class) //*Not sure about this
		{
			// is the parent of this $class a descendant of $descendant_class_id?
			$isDescendant = $isDescendant || self::isDescendant($class['class_id'], $descendant_class_id, $disallowed_classes);
		}
		return $isDescendant;
	}
	
	public static function isAncestor($class_id, $ancestor_class_id, $disallowed_classes)
	{
		// base case: ancestor_class is an ancestor of class if they are the same
		if ($class_id == $ancestor_class_id)
		{
			return true;
		}
		// get the connections in $disallowed_classes with class_ids equal to $class_id
		$classes = $disallowed_classes->where('class_id', $class_id);
		$isAncestor = false;
		// call isAncestor() with each of the classes' parents
		// (ie ask whether $ancestor_class_id is an ancestor of each class's parent)
		// and then OR all of the results together to get a final value
		foreach ($classes as $class)
		{
			// is the parent of this $class an ancestor of $ancestor_class_id?
			$isAncestor = $isAncestor || self::isAncestor($class['parent_id'], $ancestor_class_id, $disallowed_classes);
		}
		return $isAncestor;
	}
	
	public static function printAsciiDescendants($class)
	{
		if (is_int($class))
		{
			$class = Academic_Class::find($class); //This is giving an error; not sure why
		}

		echo NestedArrays::convertToAscii(NestedArrays::classDescendants($class));
	}
	
	public static function printAsciiAncestors($class)
	{
		if (is_int($class))
		{
			$class = Academic_Class::find($class); //this seems like the same line
			//as in printAsciiDescendants() but isn't giving an error
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