<?php

use App\Resource;
use App\Academic_Class;
use Illuminate\Database\Seeder;
use App\Repositories\ClassRepository;
use App\Repositories\ResourceRepository;

class ResourceClassTableSeeder extends Seeder
{
	/**
	 * What is the maximum number of resources that each class can have?
	 */
	const NUM_MAX_RESOURCES = 3;

	/**
	 * With what probability should resources be assigned to classes farther
	 * down the tree than those closer to the root?
	 * Use 1 if you want resource-class attachments to be weighted by class
	 * depth or 0 if you want resources to be assigned to classes completely
	 * randomly. A weight much greater than 1 will force resources to be at the
	 * leafs and a negative weight will put resources closer to the root, instead.
	 * For more info, see the docstring for the "scale" parameter of the wrand() function in app\Helpers\Helper.php
	 */
	const WEIGHT = 0.6;

	/**
	 * Run the database seeds.
	 *
	 * @return void
	 */
	public function run()
	{
		// big picture: iterate through each resource and pick a class for them from the allowed classes
		// since we want every resource to have at least one class
		
		// get the allowed classes
		$classes = Academic_Class::all();

		// before we attempt to calculate the depths, check whether they'll even be useful
		// this saves computational time
		if (self::WEIGHT !== 0)
		{
			// get the depths of each class for later use
			$depths = collect(ClassRepository::depths($classes, 1));
		}
		else
		{
			// it doesn't matter what our depths are, since self::WEIGHT is 0
			// so just make all of them 1
			$depths = collect(array_fill_keys($classes->pluck('id')->toArray(), 1));
		}

		Resource::all()->shuffle()->each(
			function($resource) use ($classes, $depths)
			{
				$available_classes = ResourceRepository::allowedClasses($resource);
				$class = null;
				// pick a class if one exists
				while (is_null($class) && $classes->count() > 0)
				{
					$class = $this->pickRandomClass($classes, $available_classes, $depths);
					// if this class already has too many resources
					if ($class->resources()->count() >= self::NUM_MAX_RESOURCES)
					{
						$classes->forget($classes->search($class));
						$class = null;
					}
				}
				if (!is_null($class))
				{
					// add this resource to the class
					$resource->class()->associate($class)->save();
				}
			}
		);
	}

	private function pickRandomClass($classes, $available_classes, $depths)
	{
		$classes = $classes->intersect($available_classes)->keyBy('id');
		$depths = $depths->intersectByKeys($classes);
		return $classes->get(wrand($depths, self::WEIGHT));
	}
}
