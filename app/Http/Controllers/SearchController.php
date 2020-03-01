<?php

namespace App\Http\Controllers;

use App\Resource;
use Illuminate\Http\Request;

class SearchController extends Controller
{
	/**
	 * Whether we should return JSON instead of a view.
	 * @var bool
	 */
	protected $return_JSON;

	public function __construct(Request $request)
	{
		// check whether this controller should return a view or a JSON response
		// we can check the route name to figure it out
		$this->return_JSON = $request->route()->named('search.json');
	}

	/**
	 * This function is automatically invoked by Laravel when the 
	 * controller is called.
	 * @return Collection|view 	a collection if $return_JSON is true else a view
	 */
	public function __invoke(Request $request)
	{
		// first, validate the query
		$validated = $request->validate([
			'q' => 'nullable|string'
		]);

		// now, retrieve the query
		$query = !array_key_exists('q', $validated) || is_null($validated['q']) ? '' : $validated['q'];

		// execute the query and get the converted results
		$result = Resource::search($query)->get()->map(
			function($resource) {
				// get the default resource data
				$result = $resource->toSearchableArray(false);
				// attach the id, created_at, and updated_at dates
				$result->put('id', $resource->id);
				$result->put('created_at', $resource->created_at);
				$result->put('updated_at', $resource->updated_at);
				return $result;
			}
		);

		// what should we return?
		if ($this->return_JSON)
		{
			return $result;
		}
		else
		{
			return view('search', ['search_query' => $query, 'results' => $result]);
		}
	}
}
