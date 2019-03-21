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
		
		Resource::all()->shuffle()->each(
			function($resource) use ($classes)
			{
				$available_classes = ResourceRepository::allowedClasses($resource);
				$class = null;
				// pick a class if one exists
				while (is_null($class) && $classes->count() > 0)
				{
					$class = $classes->intersect($available_classes)->random();
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
}
