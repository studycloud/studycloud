<?php

namespace App\Http\Controllers;

use App\Resource;
use Carbon\Carbon;
use App\ResourceContent;
use Illuminate\Http\Request;
use App\Http\Middleware\CheckAuthor;
use Illuminate\Contracts\Validation\Rule;
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
	}

	/**
	 * Store a newly created resource in storage.
	 *
	 * @param  \Illuminate\Http\Request  $request
	 * @return \Illuminate\Http\Response
	 */
	public function store(Request $request)
	{
		$validated = $request->validate([
			'name' => 'string|required|max:255',
			'use_id' => 'required|exists:resource_uses,id',
			'contents' => 'bail|required'
			'contents.*.name' => 'string|required|max:255',
			'contents.*.type' => [
				'required',
				Rule::in(ResourceContent::getPossibleTypes())
			],
			'contents.*.content' => 'string'
		]);

		$newResource = new Resource;
		$newResource->name = $request->name;
		$newResource->author_id = Auth::id();
		$newResource->use_id = $request->use_id;

		// maybe also create a new ResourceContent and attach it to this Resource?

		$newResource->save();
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
	 * Update the specified resource in storage.
	 *
	 * @param  \Illuminate\Http\Request  $request
	 * @param  Resource  $resource
	 * @return \Illuminate\Http\Response
	 */
	public function update(Request $request, Resource $resource)
	{
		$request->validate([
			'name' => 'sometimes|max:255'
			'use_id' => 'sometimes|exists:resource_uses,id',
			'contents.*.name' => 'sometimes|max:255',
			'contents.*.type' => [
				'sometimes',
				Rule::in(ResourceContent::getPossibleTypes())
			],
			'contents.*.content' => 'sometimes|string'
		]);

		// TODO: check which ones are null
		$resource->name = $request->name;
		$resource->author_id = Auth::id();
		$resource->use_id = $request->use_id;

		$resource->save();
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
