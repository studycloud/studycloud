<?php

use App\Notice;
use Illuminate\Database\Seeder;
use Illuminate\Database\Eloquent\Collection;

class NoticeParentTableSeeder extends Seeder
{
	/**
	 * What is the maximum number of notices that each level of the tree can have?
	 */
	const NUM_MAX_NOTICES = 3;

	/**
	 * Should the order of the notices in the notice tree be random?
	 */
	const CHOOSE_RANDOMLY = false;

	/**
	 * How many levels should the first level of the tree have?
	 * For this to work, make sure self::NUM_MAX_NOTICES is large enough that notices won't be left over after run() is run. Any left over notices are assigned to the root.
	 * Otherwise, use null if this number should be unrestricted.
	 */
	const RESTRICT_FIRST_LEVEL = null;

	/**
	 * An array of notices that haven't become parents (i.e. haven't been assigned children).
	 *
	 * @var array
	 */
	protected $notices = [];

	/**
	 * Run the database seeds.
	 *
	 * @return void
	 */
	public function run()
	{
		$this->notices = Notice::all();
		// assign children to the root of the tree (and to subtrees when we recur)
		$this->assignChildren(null, 1, $num_notices_level = self::RESTRICT_FIRST_LEVEL);
	}

	/**
	 * assign children to a parent notice
	 * @param  Notice|null	$parent					the soon-to-be parent notice
	 * @param	int					$num_notices_level_min	the min number of notices that can occur at this level of the tree; only used when $num_notices_level is null
	 * @param	int|null			$num_notices_level 		the number of notices to use at this level of tree or null if unconstrained
	 */
	private function assignChildren($parent = null, $num_notices_level_min = 1, $num_notices_level = null)
	{
		// how many notices should be at this level of the tree?
		// keep choosing a random number until the choice isn't 1
		while (
			is_null($num_notices_level) ||
			($num_notices_level == 1 && self::NUM_MAX_NOTICES != 1)
		)
		{
			$num_notices_level = rand( $num_notices_level_min, min(self::NUM_MAX_NOTICES, $this->notices->count()) );
		}
		$children = collect();
		// choose children to add to this parent
		while ($num_notices_level > 0)
		{
			$child = $this->chooseNotice();
			if (!is_null($child))
			{				
				$children->push($child);
			}
			$num_notices_level--;
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
	 * @param  Collection|null	$notices	the available children
	 * @return Notice				a randomly chosen child notice
	 */
	private function chooseNotice(&$notices = null)
	{
		$choice = null;
		// if param wasn't provided, use notice property
		if (is_null($notices))
		{
			$notices = $this->notices;
		}
		if ($notices->count()>0)
		{		
			// choose a notice
			if (self::CHOOSE_RANDOMLY)
			{
				$choice = $notices->random();
				// now that we've chosen this notice, let's remove it from those that are available
				$notices->forget($notices->search($choice, true));
			}
			else
			{
				$choice = $notices->shift();
			}
		}
		return $choice;
	}
}
