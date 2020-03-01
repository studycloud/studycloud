<?php

namespace App\Http\Controllers;

use App\Academic_Class;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Auth;
use App\Rules\ValidClassParentAttachment;
use Illuminate\Support\Facades\Validator;
use App\Rules\ValidClassChildrenAttachment;

class ClassController extends Controller
{
	/**
	 * ROUTES FOR THIS CONTROLLER
	 *	HTTP Verb	URI						Route Name		Action
	 *	GET			/classes				classes.index	show the class tree page
	 *	GET			/classes/create			classes.create	show the class creation page
	 *	POST		/classes				classes.store	create a new class sent as JSON
	 *	GET			/classes/{id}			classes.show	show the page for this class
	 *	GET			/classes/{id}/edit		classes.edit	show the editor for this class
	 *	PATCH/PUT	/classes/{id}			classes.update	alter a current class to match the attributes sent as JSON
	 *	DELETE		/classes/{id}			classes.destroy	request that this class be deleted
	 *	PATCH		/classes/attach/{id}	classes.attach	alter either the parent or children of this class
	 */

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
				'integer',
				'required',
				Rule::in(
					Academic_Class::pluck('id')->push(0)->toArray()
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
		// check that the author of this class is not null
		// a null author indicates that the class is not editable by the average user
		// TODO: convert this check to an auth rule
		Validator::make($class->toArray(),
			[
				'author_id' => 'required'
			],
			[
				'author_id' => "This class was created by the administrators. You cannot edit it."
			]
		)->validate();

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
		// check that the author of this class is not null
		// a null author indicates that the class is not editable by the average user
		// TODO: convert this check to an auth rule
		Validator::make($class->toArray(),
			[
				'author_id' => 'required'
			],
			[
				'author_id.required' => "This class was created by the administrators. You cannot edit it."
			]
		)->validate();

		// TODO: make custom validation logic for the stuff below?
		// before deleting the class, make sure it doesn't have any classes attached underneath it
		// also make sure it doesn't have any resources attached to it
		Validator::make([
			'children_count' => $class->children()->count(),
			'resources_count' => $class->resources()->count()
		], [
			'children_count' => 'integer|max:0',
			'resources_count' => 'integer|max:0'
		], [
			'children_count.max' => 'You cannot delete a class that has children.',
			'resources_count.max' => 'You cannot delete a class that has resources.'
		])->validate();
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
		$all_classes = Academic_Class::pluck('id');
		// first, validate the request
		// note that the parent attribute can either be empty or 0,
		// which would mean that we must attach the root as the parent
		$validated = $request->validate([
			'parent' => [
				'bail',
				'integer',
				'required_without:children',
				Rule::in(
					$all_classes->push(0)->reject($id)->toArray()
				),
				new ValidClassParentAttachment($class)
			],
			'children' => 'array|required_without:parent',
			'children.*' => [
				'bail',
				'integer',
				'distinct',
				'required',
				Rule::in(
					$all_classes->reject($id)->toArray()
				)
			]
		]);

		// now that we have validated the request, let's finish validating the children
		// make sure to pass it the validated parent, if there is one
		Validator::make($request->all(),
			[
				'children' => [
					new ValidClassChildrenAttachment(
						$class,
						array_key_exists('parent', $validated) ? $validated['parent'] : null
					)
				]
			]
		)->validate();

		// before attaching the parent,
		// first check that the class is not the root
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
		}
		// attach the children if needed
		if (array_key_exists('children', $validated))
		{
			$id = $id === 0 ? null : $id;
			// attach all the children
			// as of laravel 6, this may not prevent against mass assignment anymore! (see https://laravel.com/docs/6.x/upgrade#eloquent)
			Academic_Class::whereIn('id', $validated['children'])->update(['parent_id' => $id]);
		}
	}
}
