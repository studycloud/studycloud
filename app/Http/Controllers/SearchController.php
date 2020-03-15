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

	/**
	 * Whether to interpret all queries literally by escaping reserved characters.
	 * Setting this value to true will disable fancy searching.
	 * @var bool
	 */
	protected $literal_queries = false;

	/**
	 * At most how many hits should we return?
	 * @var integer
	 */
	protected $number_of_hits = 20;

	/**
	 * A list of all available fields and their contributions to the overall search score.
	 * A field's weight will default to 0 if not specified, so specify all of them!
	 * Set this variable to a falsey value to assign all fields the same weight of 1.
	 * @var array
	 */
	protected $field_weights = [
		"name" => 2,
		"author" => 1,
		"use" => 0.5,
		"classes" => 0.5,
		"contents.name" => 2,
		"contents.type" => 0.25,
		"contents.content" => 1
	];


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
		$query_unescaped = !array_key_exists('q', $validated) || is_null($validated['q']) ? '' : $validated['q'];

		if ($this->literal_queries)
		{
			$query = $this->escape_elastic_search_reserved_chars($query_unescaped);
		}
		else
		{
			$query = $query_unescaped;
		}

		// execute the query and get the converted results
		$result = Resource::search($query,
			function($client, $body) {
				// set the number of desired hits
				$body->setSize($this->number_of_hits);
				// weight each field accordingly
				if ($this->field_weights)
				{
					$body->getQueries()->setParameters(['type' => 'most_fields', 'fields' => array_map(
							function($k, $v){
								return "$k^$v";
							},
							array_keys($this->field_weights),
							array_values($this->field_weights)
						)
					]);
				}
				return $client->search(['index' => (new Resource)->searchableAs(), 'body' => $body->toArray()]);
			}
		)->get()->map(
			function($resource) {
				// get the default resource data
				$result = $resource->toSearchableArray(false);
				// attach the id, created_at, and updated_at dates
				$result->put('id', $resource->id);
				$result->put('created_at', $resource->created_at->format('M j, Y g:i A'));
				$result->put('updated_at', $resource->updated_at->format('M j, Y g:i A'));
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
			return view('search', ['search_query' => $query, 'search_query_unescaped' => $query_unescaped, 'results' => $result]);
		}
	}

	/**
	 * See https://stackoverflow.com/a/33846134
	 * Elasticsearch has a number of reserved characters that can be escaped for more intuitive searches
	 */
	private function escape_elastic_search_reserved_chars($string) {
		$regex = "/[\\+\\-\\=\\&\\|\\!\\(\\)\\{\\}\\[\\]\\^\\\"\\~\\*\\<\\>\\?\\:\\\\\\/]/";
		$string = preg_replace_callback ($regex,
			function ($matches) {
				return "\\" . $matches[0];
			}, $string);
		return $string;
	}
}
