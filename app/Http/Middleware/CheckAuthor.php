<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Auth;

/**
 * check that the author of an item is the authenticated user
 */
class CheckAuthor
{
	/**
	 * Handle an incoming request.
	 *
	 * @param	\Illuminate\Http\Request	$request
	 * @param	\Closure					$next
	 * @return mixed
	 */
	public function handle($request, Closure $next)
	{
		// assume that the first route parameter is the id of the item
		$item = $request->route()->parameters();
		// is there an item to check?
		if (count($item) == 1)
		{
			// get the item
			$item = reset($item);
			// assume that the item has an author attribute
			if ($item->author->id == Auth::id())
			{
				return $next($request);
			}
			abort(403, "You aren't authorized to perform this action.");
		}
		abort(400);
	}
}
