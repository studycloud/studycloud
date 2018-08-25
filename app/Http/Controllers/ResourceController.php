<?php

namespace App\Http\Controllers;

use App\Resource;
use Carbon\Carbon;
use App\ResourceContent;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use App\Http\Middleware\CheckAuthor;
use Illuminate\Support\Facades\Auth;
use Illuminate\Database\Eloquent\Collection;


class ResourceController extends Controller
{
	/**
	ROUTES FOR THIS CONTROLLER
		HTTP Verb		URI						Route Name			Action
		GET				/resources/create		resources.create	show the resource creation page
		POST			/resources				resources.store		create a new resource sent as JSON
		GET				/resources/{id}			resources.show		show the page for this resource (and the editor if logged in as the author)
		PATCH (or PUT)	resources/{id}			resources.update	alter a current resource according to the changes sent as JSON
		DELETE			/resources/{id}			resources.destroy	request that this resource be deleted
	**/

	function __construct()
	{
		// verify that the user is signed in for all methods except index, show, and json
		$this->middleware('auth', ['except' => ['index', 'show']]);
		// verify that the user is the author of the resource
		$this->middleware(CheckAuthor::class, ['only' => ['update', 'move', 'destroy']]);
	}

	/**
	 * Display a listing of the resource, so the user can pick a resource among the many available.
	 *
	 * @return \Illuminate\Http\Response
	 */
	public function index()
	{
		// this method is currently not accessible from a route
		// it has been disabled
	}

	/**
	 * Show the form for creating a new resource.
	 *
	 * @return \Illuminate\Http\Response
	 */
	public function create()
	{
		// load the appropriate view here
		// return view('resource.create', ['resource' => NULL]);
	}

	/**
	 * Store a newly created resource in storage.
	 *
	 * @param  \Illuminate\Http\Request  $request
	 * @return \Illuminate\Http\Response
	 */
	public function store(Request $request)
	{
		// first, validate the request
		$validated = $request->validate([
			'name' => 'string|required|max:255',
			'use_id' => 'required|exists:resource_uses,id',
			'contents' => 'required|array',
			'contents.*.name' => 'string|required|max:255',
			'contents.*.type' => [
				'required',
				Rule::in(ResourceContent::getPossibleTypes())
			],
			'contents.*.content' => 'string'
		]);

		// create a new Resource using mass assignment to add 'name' and 'use_id' attributes
		$resource = (new Resource)->fill($validated);
		$resource->author_id = Auth::id();
		$resource->save();
		// create new ResourceContents and attach them to this Resource
		$contents = [];
		foreach ($validated["contents"] as $content) {
			// use mass assignment to add 'name', 'type', and 'content' attributes
			$contents[] = (new ResourceContent)->fill($content);
		}
		$resource->contents()->saveMany($contents);
	}

	/**
	 * Display the specified resource.
	 *
	 * @param  Resource  $resource
	 * @return \Illuminate\Http\Response
	 */
	public function show(Resource $resource)
	{
		return view('resource', ['resource' => $resource]);
	}

	/**
	 * Show the form for editing the specified resource.
	 *
	 * @param  Resource  $resource
	 * @return \Illuminate\Http\Response
	 */
	public function edit(Resource $resource)
	{
		// load the same view as the create method
		// return view('resource.create', ['resource' => $resource]);
	}

	/**
	 * Update the specified resource in storage.
	 *
	 * @param  \Illuminate\Http\Request  $request
	 * @param  Resource  $resource
	 * @return \Illuminate\Http\Response
	 */
	public function update(Request $request, Resource $resource)
	{
		// first, validate the request
		$validated = $request->validate([
			'name' => 'sometimes|max:255',
			'use_id' => 'sometimes|exists:resource_uses,id',
			'contents' => 'sometimes|array',
			'contents.*.id' => [
				'required_with:contents.*.name,contents.*.type,contents.*.content',
				Rule::exists('resource_contents', 'id')->where(
					function ($query) use ($resource)
					{
						// check that this resource content actually belongs to the resource
						$query->where('resource_id', $resource->id);
					}
				)
			],
			'contents.*.name' => 'sometimes|max:255',
			'contents.*.type' => [
				'sometimes',
				Rule::in(ResourceContent::getPossibleTypes())
			],
			'contents.*.content' => 'sometimes|string'
		]);

		// update whichever resource attributes have been sent in the request
		// note that this uses mass assignment. see the $fillable array on the Resource to see which attributes are allowed
		$resource->fill($validated)->save();
		// update resource content attributes
		if (array_key_exists("contents", $validated))
		{
			foreach ($validated["contents"] as $content) {
				// use mass assignment to update 'name', 'type', or 'content' attributes
				ResourceContent::find($content['id'])->fill($content)->save();
			}
		}
	}

	/**
	 * Moves the resource into the desired topics. If attempting to 
	 * move into a topic that is an ancestor or child of the resource's
	 * current topics, the conflicting topics will be removed, as well.
	 *
	 * @param Resource $resource
	 * @param Topic $topic
	 * @return \Illuminate\Http\Response
	 *
	 *
	 */
	public function move(Resource $resource, Topic $topic)
	{
		// do stuff
	}


	/**
	 * Remove the specified resource from storage.
	 *
	 * @param  Resource  $resource
	 * @return \Illuminate\Http\Response
	 */
	public function destroy(Resource $resource)
	{
		// first, delete attachments this resource has to any topics
		$resource->topics->pluck('pivot')->each(
			function ($resource_topic)
			{
				$resource_topic->delete();
			}
		);
		// also delete the resource's contents
		$resource->contents->each(
			function ($content)
			{
				$content->delete();
			}
		);
		// finally, we can delete the resource
		$resource->delete();
	}
}
