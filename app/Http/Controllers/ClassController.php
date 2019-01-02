<?php

namespace App\Http\Controllers;

use App\Academic_Class;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Auth;

class ClassController extends Controller
{
	/**
	ROUTES FOR THIS CONTROLLER
		HTTP Verb	URI	Route Name	Action
		
	**/

	function __construct()
	{
		// verify that the user is signed in for all methods except index, show, and json
		$this->middleware('auth', ['except' => ['index', 'show']]);

		// TODO: add CheckStatus middleware?
	}

	/**
	 * Display a listing of the class.
	 *
	 * @return \Illuminate\Http\Response
	 */
	public function index()
	{
		// currently this view doesn't exist, but maybe it should?
		// return view('classes');
	}

	/**
	 * Show the form for creating a new class.
	 *
	 * @return \Illuminate\Http\Response
	 */
	public function create()
	{
		// return a view for creating a new class
		// currently this view doesn't exist, but it probably should
	}

	/**
	 * Store a newly created class in storage.
	 *
	 * @param  \Illuminate\Http\Request  $request
	 * @return \Illuminate\Http\Response
	 */
	public function store(Request $request)
	{
		// first, validate the request
		// note that the parent attribute can be 0,
		// which would mean that we must attach the root as the parent
		$validated = $request->validate([
			'name' => 'string|required|max:255',
			'parent' => [
				'int',
				'required',
				Rule::in(
					Academic_Class::pluck('id')->push('0')->toArray()
				)
			]
		]);

		// create a new Academic_Class using mass assignment to add the 'name' attribute
		$class = (new Academic_Class)->fill($validated);
		$class->author_id = Auth::id();
		// check that the parent attribute is not 0
		// otherwise, don't set the parent attribute, since it will default to NULL
		if ($validated['parent'])
		{
			$class->parent()->associate($validated['parent']);
		}
		$class->save();
	}

	/**
	 * Display the specified class.
	 *
	 * @param  \App\Academic_Class  $academic_Class
	 * @return \Illuminate\Http\Response
	 */
	public function show(Academic_Class $class)
	{
		// let the js handle parsing the URL to determine which class to retrieve
		// currently this view doesn't exist, but maybe it should?
		// return view('classes');
	}

	/**
	 * Show the form for editing the specified class.
	 *
	 * @param  \App\Academic_Class  $class
	 * @return \Illuminate\Http\Response
	 */
	public function edit(Academic_Class $class)
	{
		// return a view for editing a topic
		// perhaps this functionality should be embedded in the class tree, though?
	}

	/**
	 * Update the specified class in storage.
	 *
	 * @param  \Illuminate\Http\Request  $request
	 * @param  \App\Academic_Class  $class
	 * @return \Illuminate\Http\Response
	 */
	public function update(Request $request, Academic_Class $class)
	{
		// first, validate the request
		// note that we make the 'name' attribute required because there aren't any other attributes to validate
		$validated = $request->validate([
			'name' => 'string|required|max:255'
		]);

		// create a new Class using mass assignment to add the 'name' attribute
		$class = $class->fill($validated);
		$class->author_id = Auth::id();
		$class->save();
	}

	/**
	 * Remove the specified class from storage.
	 *
	 * @param  \App\Academic_Class  $class
	 * @return \Illuminate\Http\Response
	 */
	public function destroy(Academic_Class $class)
	{
		// TODO: make custom validation logic for the stuff below?
		// before deleting the class, make sure it doesn't have any classes attached underneath it
		if ($class->children()->count() > 0)
		{
			abort(405, "You cannot delete a class that has children");
		}
		// also make sure it doesn't have any resources attached to it
		if ($class->resources()->count() > 0)
		{
			abort(405, "You cannot delete a class that has resources");
		}
		// actually delete the class
		$class->delete();
	}

	/**
	 * Attach this class as a child of a new parent class, (overriding the existing parent)
	 * 
	 * @param  \Illuminate\Http\Request  $request
	 * @param  Academic_Class $class the child class
	 * @return \Illuminate\Http\Response
	 */
	public function attach(Request $request, Academic_Class $class = null)
	{
		$id = is_null($class) ? 0 : $class->id;
		// first, validate the request
		// note that the parent attribute can either be empty or 0,
		// which would mean that we must attach the root as the parent
		$validated = $request->validate([
			'children' => 'array|required_without:parent',
			'children.*' => [
				'int',
				'distinct',
				'required',
				Rule::in(
					Academic_Class::pluck('id')->reject($id)->toArray()
				)
			],
			'parent' => [
				'int',
				'required_without:children',
				Rule::in(
					Academic_Class::pluck('id')->push('0')->reject($id)->toArray()
				)
			]
		]);

		// first, check that the class is not the root
		if ($id != 0)
		{
			if (array_key_exists('parent', $validated))
			{
				// check that the parent is not the root (ie 0)
				if ($validated['parent'])
				{
					$class->parent()->associate($validated['parent']);
				}
				else
				{
					$class->parent()->dissociate();
				}
				$class->save();
			}
			if (array_key_exists('children', $validated))
			{
				// attach all the children
				// but first, convert all of the children IDs to model instances
				// TODO: make this more efficient - currently it executes 2 queries, but it could be 1
				$children = Academic_Class::whereIn('id', $validated['children'])->get();
				$class->children()->saveMany($children);
			}
		}
		elseif (array_key_exists('children', $validated))
		{
			Academic_Class::whereIn('id', $validated['children'])->update(['parent_id' => null]);
		}
	}
}
