<?php

namespace App\Rules;

use App\Academic_Class;
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
	 * the id of the new parent of $this->class
	 * @var int|null
	 */
	protected $parent;

	/**
	 * an array of IDs for the children to attach to $this->class
	 * @var array
	 */
	protected $children;

	/**
	 * all parents and children of $this->class in the separated connections format
	 * @var Collection
	 */
	protected $tree;

	/**
	 * Create a new class attachment rule instance.
	 *
	 * @param   Academic_Class|null $class      the class we want to attach things to or null for the root
	 * @param   int|null            $parent     the id of the new parent class or null if we shouldn't replace the current one
	 * @param   array               $children   an array of class id's that should be attached as children to this class
	 * @param   Collection|null     $tree       all parents and children of $class in the separated connections format; if null, queries will be performed to retrieve the required data
	 * @return void
	 */
	public function __construct(Acsademic_Class $class=null, int $parent=null, $children=[], Collection $tree=null)
	{
		$this->class = $class;
		$this->parent = $parent;
		$this->children = $children;
		$this->tree = $tree;
	}

	/**
	 * Determine if the validation rule passes. Check whether we can change the parent and children of a class all at once.
	 *
	 * @param  string  $attribute
	 * @param  mixed  $value
	 * @return bool
	 */
	public function passes($attribute, $value)
	{
		// if we're dealing with the root class
		if (is_null($this->class) || $this->class->id == 0)
		{
			// the root class cannot have a parent
			if (!is_null($this->parent))
			{
			   $this->message = "The root class cannot be assigned a parent";
			   return false;
			}
			// you can attach any children to the root
			return true;
		}
		$get_tree = false;
		// get the descendants that are required
		if (is_null($this->tree))
		{
			$get_tree = true;
			$this->tree = NodesAndConnections::treeAsSeparatedConnections(collect(), (new ClassRepository)->descendants($this->class));
		}
		// return failure if the new parent is a descendant of $this->class
		// make sure to include the new children when you check
		if (self::isDescendant($this->class->id, $this->parent, $this->tree))
		{
			$this->message = "Class ".$this->parent." is a descendant of class ".$this->class->id.". It cannot be added as its parent.";
			return false;
		}
		// get the ancestors that are required
		if ($get_tree)
		{
			// check if we need to retrieve the new ancestors
			if (!is_null($this->parent))
			{
				if ($this->parent !== 0)
				{
					// get the ancestors of the new parent
					$this->tree['ancestors'] = NodesAndConnections::treeAsConnections(
						(new ClassRepository)->ancestors(
							Academic_Class::find($this->parent), null, Academic_Class::getRoot()
						)
					);
				}
				// add the connection between the current parent and the new parent
				$this->tree['ancestors']->prepend(
					collect([
						'parent_id' => $this->parent,
						'class_id' => $this->class->id
					])
				);
			}
			else    // retrieve the current ancestors
			{
				$this->tree['ancestors'] = NodesAndConnections::treeAsConnections(
					(new ClassRepository)->ancestors(
						$this->class, null, Academic_Class::getRoot()
					)
				);
			}
		}
		// find all new children that are ancestors of $this->class
		$bad_children = self::isAncestor($this->class->id, $this->children, $this->tree);
		if (count($bad_children) > 1)
		{
			if (is_null($this->parent))
			{
				$this->message = "Classes ".readable_array($bad_children)." are ancestors of class ".$this->class->id.". They cannot be added as its children.";
				return false;
			}
			else
			{
				$this->message = "By making class ".$this->parent." the parent of class ".$this->class->id.", classes ".readable_array($bad_children)." will become ancestors of class ".$this->class->id.". They cannot be added as its children.";
				return false;
			}
		}
		elseif (count($bad_children) == 1)
		{
			if (is_null($this->parent))
			{
				$this->message = "Class ".$bad_children[0]." is an ancestor of class ".$this->class->id.". It cannot be added as its child.";
				return false;
			}
			else
			{
				$this->message = "By making class ".$this->parent." the parent of class ".$this->class->id.", class ".$bad_children[0]." will become an ancestor of class ".$this->class->id.". It cannot be added as its child.";
				return false;
			}
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
