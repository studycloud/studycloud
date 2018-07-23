<?php

namespace App\Http\Controllers;

use App\Resource;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Database\Eloquent\Collection;


class ResourceController extends Controller
{
	/**
	ROUTES FOR THIS CONTROLLER
		HTTP Verb		URI						Route Name			Action
		GET				/resources/create		resources.create	show the resource creation page
		POST			/resources				resources.store		create a new resource sent as JSON
		GET				/resources/{id}			resources.show		show the page for this resource (and the editor if logged in as the author)
		POST			/resources/data/{id}	resources.json		get the JSON representation of this resource
		PATCH (or PUT)	resources/{id}			resources.update	alter a current resource according to the changes sent as JSON
		DELETE			/resources/{id}			resources.destroy	request that this resource be deleted
	**/

	function __construct(Resource $resource)
	{
		// verify that the user is signed in for all methods except index, show, and json
		$this->middleware('auth', ['except' => ['index', 'show', 'json']]);
		// verify that the user is the author of the resource
		$this->middleware(
			function ($request, $next) use ($resource)
			{
				if ($resource->author == Auth::user())
				{
					return $next($request);
				}
				abort(403, "You aren't authorized to perform this action.");
			},
		['only' => ['update', 'move', 'destroy']]);
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
	 * convert a resource to JSON
	 * @param  Resource	$resource	the resource to display
	 * @return Collection			the json representation of this resource
	 */
	public function json(Resource $resource)
	{
		$json = collect();
		$author = $resource->author;
		// set meta tag that specifies the resource's meta data
		$json['meta'] = collect([
							"name" => $resource->name,
							"author_name" => $author->name(),
							"author_type" => $author->type
						]);
		// set contents of this resource to be
		// a list of collections each with the following attributes:
		// 		name
		// 		type
		// 		content
		// 		created - formatted as April 1, 2018 1:05 AM
		// 		updated - formatted as April 1, 2018 1:05 AM
		$json['contents'] = $resource->contents->map(
			function($content)
			{
				$new_content = collect($content);
				$new_content['created'] = $content['created_at']->format('M j, Y g:i A');
				$new_content['updated'] = $content['updated_at']->format('M j, Y g:i A');
				return $new_content->only(['name', 'type', 'content', 'created', 'updated']);
			}
		);
		return $json;
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
		//Request must have information about name, author_id, and use_id.
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
		$resource->delete();
	}
}
