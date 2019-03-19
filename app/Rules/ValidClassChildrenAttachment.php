<?php

namespace App\Rules;

use App\Academic_Class;
use App\Helpers\NodesAndConnections;
use App\Repositories\ClassRepository;
use Illuminate\Contracts\Validation\Rule;
use Illuminate\Database\Eloquent\Collection;

class ValidClassChildrenAttachment implements Rule
{
	/**
	 * the error message if validation does not pass
	 * @var string
	 */
	protected $message = "The child class attachment(s) is/are invalid.";

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
	 * @param   int|null  $parent     the id of the new parent class or null if it's the same as it is currently
	 * @param   Collection|null     $tree       all parents and children of $class in the separated connections format; if null, queries will be performed to retrieve the required data
	 * @return void
	 */
	public function __construct(Academic_Class $class=null, int $parent=null, Collection $tree=null)
	{
		$this->class = $class;
		$this->class_is_root = is_null($this->class) || $this->class->id == 0;
		$this->parent = $parent;
		$this->tree = (is_null($tree) and !$this->class_is_root) ? $this->getTree() : $tree;
	}

	/**
	 * retrieve the necessary connections in separated connections format
	 * @return Collection             the separated connections of the ancestors of $this->class, given that $parent is its parent
	 */
	private function getTree()
	{
		$tree = NodesAndConnections::treeAsSeparatedConnections(collect(), collect());
		// check if we need to retrieve the new ancestors
		if (!is_null($this->parent))
		{
			if ($this->parent !== 0)
			{
				// get the ancestors of the new parent
				$tree['ancestors'] = NodesAndConnections::treeAsConnections(
					(new ClassRepository)->ancestors(
						Academic_Class::find($this->parent), null, Academic_Class::getRoot()
					)
				);
			}
			// add the connection between the current parent and the new parent
			$tree['ancestors']->prepend(
				collect([
					'parent_id' => $this->parent,
					'class_id' => $this->class->id
				])
			);
		}
		else    // retrieve the current ancestors
		{
			$tree['ancestors'] = NodesAndConnections::treeAsConnections(
				(new ClassRepository)->ancestors(
					$this->class, null, Academic_Class::getRoot()
				)
			);
		}
		return $tree;
	}

	/**
	 * Determine if the validation rule passes. Check whether we can add these children to this class, assuming $this->parent is its new parent.
	 *
	 * @param  string  $attribute
	 * @param  mixed   $children   an array of class id's that should be attached as children to this class
	 * @return bool
	 */
	public function passes($attribute, $children)
	{
		// check if we're dealing with the root class
		// you can attach any children to the root
		if (!$this->class_is_root)
		{
			// find all new children that are ancestors of $this->class
			$bad_children = ClassRepository::isAncestor($this->class->id, $this->children, $this->tree);
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
