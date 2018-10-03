<?php

namespace App\Repositories;

use App\Academic_Class;
use App\Helpers\NestedArrays;

class ClassRepository 
{
    public static function getTopLevelClasses()
    {
        return Academic_Class::whereNotExists(function ($query)
            {
                $query->select('class_id')->distinct()->from('class_parent')->whereRaw('class_parent.class_id = classes.id');
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