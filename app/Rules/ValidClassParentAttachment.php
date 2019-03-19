<?php

namespace App\Rules;

use App\Academic_Class;
use App\Helpers\NodesAndConnections;
use App\Repositories\ClassRepository;
use Illuminate\Contracts\Validation\Rule;
use Illuminate\Database\Eloquent\Collection;

class ValidClassAttachment implements Rule
{
	/**
	 * the error message if validation does not pass
	 * @var string
	 */
	protected $message = "The class attachment is invalid";

	/**
	 * the class we are attaching things to
	 * @var Academic_Class
	 */
	protected $class;

	/**
	 * all parents and children of $this->class in the separated connections format
	 * @var Collection
	 */
	protected $tree;

	/**
	 * whether $this->class is the root
	 * @var boolean
	 */
	protected $class_is_root = false;

	/**
	 * Create a new class attachment rule instance.
	 *
	 * @param   Academic_Class|null $class      the class we want to attach things to or null for the root
	 * @param   Collection|null     $tree       all parents and children of $class in the separated connections format; if null, queries will be performed to retrieve the required data
	 * @return void
	 */
	public function __construct(Academic_Class $class=null, Collection $tree=null)
	{
		$this->class = $class;
		$this->class_is_root = is_null($this->class) || $this->class->id == 0;
		$this->tree = (is_null($tree) and !$this->class_is_root) ? $this->getTree() : $tree;
	}

	/**
	 * retrieve the necessary connections in separated connections format
	 * @return Collection   the separated connections of the descendants of $this->class
	 */
	private function getTree()
	{
		return NodesAndConnections::treeAsSeparatedConnections(collect(), (new ClassRepository)->descendants($this->class));
	}

	/**
	 * Determine if the validation rule passes. Check whether we can change the parent this class.
	 *
	 * @param  string    $attribute
	 * @param  int|null  $parent     the id of the new parent class or null if we shouldn't replace the current one
	 * @return bool
	 */
	public function passes($attribute, $parent=null)
	{
		// the root class cannot have a parent
		if ($this->class_is_root and is_null($this->parent))
		{
		   $this->message = "The root class cannot be assigned a parent.";
		   return false;
		}
		// return failure if the new parent is a descendant of $this->class
		// make sure to include the new children when you check
		if (ClassRepository::isDescendant($this->class->id, $this->parent, $this->tree))
		{
			$this->message = "Class ".$this->parent." is a descendant of class ".$this->class->id.". It cannot be added as its parent.";
			return false;
		}
		return true;
	}

	/**
	 * Get the validation error message.
	 *
	 * @return string
	 */
	public function message()
	{
		return $this->message;
	}
}
