<?php

use App\Academic_Class;
use Illuminate\Database\Seeder;
use Illuminate\Database\Eloquent\Collection;

class ClassParentTableSeeder extends Seeder
{
	/**
	 * What is the maximum number of classes that each level of the tree can have?
	 */
	const NUM_MAX_CLASSES = 4;

	/**
	 * Should the order of the classes in the class tree be random?
	 */
	const CHOOSE_RANDOMLY = false;

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
		$this->assignChildren($this->chooseClass());
	}

	/**
	 * assign children to a parent class
	 * @param  Academic_Class	$parent	the soon-to-be parent class
	 */
	private function assignChildren($parent, $num_classes_level_min= 1)
	{
		if (!is_null($parent))
		{
			// how many classes should be at this level of the tree?
			$num_classes_level = rand( $num_classes_level_min, min(self::NUM_MAX_CLASSES, $this->classes->count()) );
			$children = collect();
			// choose children to add to this parent
			while ($num_classes_level > 0)
			{
				$child = $this->chooseClass();
				if (!is_null($child))
				{				
					$children->push($child);
					$this->assignChildren($child, 0);
				}
				$num_classes_level--;
			}
			// add these children to this parent
			$parent->children()->attach($children->pluck('id'));
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
