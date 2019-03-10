<?php

use App\Academic_Class;
use Illuminate\Database\Seeder;
use Illuminate\Database\Eloquent\Collection;

class ClassParentTableSeeder extends Seeder
{
	/**
	 * What is the maximum number of classes that each level of the tree can have?
	 */
	const NUM_MAX_CLASSES = 3;

	/**
	 * Should the order of the classes in the class tree be random?
	 */
	const CHOOSE_RANDOMLY = false;

	/**
	 * How many levels should the first level of the tree have?
	 * For this to work, make sure self::NUM_MAX_CLASSES is large enough that classes won't be left over after run() is run. Any left over classes are assigned to the root.
	 * Otherwise, use null if this number should be unrestricted.
	 */
	const RESTRICT_FIRST_LEVEL = null;

	/**
	 * An array of classes that haven't become parents (i.e. haven't been assigned children).
	 *
	 * @var array
	 */
	protected $classes = [];

	/**
	 * Run the database seeds.
	 *
	 * @return void
	 */
	public function run()
	{
		$this->classes = Academic_Class::all();
		// assign children to the root of the tree (and to subtrees when we recur)
		$this->assignChildren(null, 1, $num_classes_level = self::RESTRICT_FIRST_LEVEL);
	}

	/**
	 * assign children to a parent class
	 * @param  Academic_Class|null	$parent					the soon-to-be parent class
	 * @param	int					$num_classes_level_min	the min number of classes that can occur at this level of the tree; only used when $num_classes_level is null
	 * @param	int|null			$num_classes_level 		the number of classes to use at this level of tree or null if unconstrained
	 */
	private function assignChildren($parent = null, $num_classes_level_min = 1, $num_classes_level = null)
	{
		// how many classes should be at this level of the tree?
		// keep choosing a random number until the choice isn't 1
		while (is_null($num_classes_level) || $num_classes_level == 1)
		{
			$num_classes_level = rand( $num_classes_level_min, min(self::NUM_MAX_CLASSES, $this->classes->count()) );
		}
		$children = collect();
		// choose children to add to this parent
		while ($num_classes_level > 0)
		{
			$child = $this->chooseClass();
			if (!is_null($child))
			{				
				$children->push($child);
			}
			$num_classes_level--;
		}
		// now assign children to the children
		foreach ($children as $child)
		{
			// recursive call!
			$this->assignChildren($child, 0);
		}
		// add these children to this parent
		// don't add them if we are at the root of the tree
		if (!is_null($parent))
		{
			$parent->children()->saveMany($children);
		}
	}

	/**
	 * pick a child (which hasn't been picked before) from the list of children
	 * @param  Collection|null	$classes	the available children
	 * @return Academic_Class				a randomly chosen child class
	 */
	private function chooseClass(&$classes = null)
	{
		$choice = null;
		// if param wasn't provided, use class property
		if (is_null($classes))
		{
			$classes = $this->classes;
		}
		if ($classes->count()>0)
		{		
			// choose a class
			if (self::CHOOSE_RANDOMLY)
			{
				$choice = $classes->random();
				// now that we've chosen this class, let's remove it from those that are available
				$classes->forget($classes->search($choice, true));
			}
			else
			{
				$choice = $classes->shift();
			}
		}
		return $choice;
	}
}
