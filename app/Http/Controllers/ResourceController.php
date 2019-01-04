<?php

namespace App\Http\Controllers;

use App\Topic;
use App\Resource;
use Carbon\Carbon;
use App\ResourceContent;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use App\Http\Middleware\CheckAuthor;
use App\Http\Middleware\CheckStatus;
use Illuminate\Support\Facades\Auth;
use App\Repositories\ResourceRepository;
use Illuminate\Database\Eloquent\Collection;


class ResourceController extends Controller
{
	/**
	 * ROUTES FOR THIS CONTROLLER
	 *	HTTP Verb	URI						Route Name			Action
	 *	GET			/resources/create		resources.create	show the resource creation page
	 *	POST		/resources				resources.store		create a new resource sent as JSON
	 *	GET			/resources/{id}			resources.show		show the page for this resource
	 *	GET			/resources/{id}/edit	resources.edit		show the editor for this resource (if logged in as the author)
	 *	PATCH/PUT	/resources/{id}			resources.update	alter a current resource according to the changes sent as JSON
	 *	PATCH		/resources/attach/{id}	resources.attach	add this resource to a list of topics (or a class) sent as JSON (overriding any conflicts that are currently attached)
	 *	PATCH		/resources/detach/{id}	resources.detach	remove this resource from a list of topics (or a class) sent as JSON
	 *	DELETE		/resources/{id}			resources.destroy	request that this resource be deleted 
	 */

	function __construct()
	{
		// verify that the user is signed in for all methods except index, show, and json
		$this->middleware('auth', ['except' => ['index', 'show']]);
		// verify that the resource isn't disabled before doing anything with it
		$this->middleware(CheckStatus::class, ['except' => ['index', 'create', 'store']]);
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
		$this->authorize('create', Resource::class);
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
		$this->authorize('create', Resource::class);
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
		// $this->authorize('view', $resource);
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
		$this->authorize('update', $resource);
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
		$this->authorize('update', $resource);
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
	 * Remove the specified resource from storage.
	 *
	 * @param  Resource  $resource
	 * @return \Illuminate\Http\Response
	 */
	public function destroy(Resource $resource)
	{
		$this->authorize('delete', $resource);
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

	/**
	 * Attach a resource to some topics or classes (or both!) in the tree, overwriting any current topics or classes that conflict
	 * @param Resource $resource
	 * @param \Illuminate\Http\Request  $request
	 */
	public function attach(Resource $resource, Request $request){
		// only the author of the resource can alter what it is attached to
		$this->authorize('update', $resource);
		// validating the request
		// topics and classes are both optional, but topics should be an array of IDs when provided
		$validated = $request->validate([
			'topics' => 'array|required_without:class',
			'topics.*' => 'exists:topics,id',
			'class' => [
				'required_without:topics',
				'integer',
				Rule::in(
					ResourceRepository::allowedClasses($resource->id)->pluck('id')->toArray()
				)
			],
		]);

		// add the topics (this code is disabled for MVP)
		// foreach($validated['topics'] as $topic){
		// 	ResourceRepository::addTopic(Topic::find($topic), $resource);
		// }
		// add the class
		ResourceRepository::attachClass($resource, $validated['class']);
	}

	/**
	 * Detach a resource from topics or classes (or both!) in the tree
	 * @param  Resource $resource
	 * @param  Request  $request
	 */
	public function detach(Resource $resource, Request $request){
		// only the author of resource can alter what it is attached to
		$this->authorize('update', $resource);
		// validating the reqeust 
		// topics and classes are both optional, but topics should be an array of IDs when provided
		$validated = $request->validate([
			'topics' => 'array|required_without:class',
			'topics.*' => 'exists:topics,id',
			'class' => 'required_without:topics|boolean',
		]);

		// remove the topics (this code is disabled for MVP)
		// ResourceRepository::detachTopics($resource, $validated['topics']);
		// remove the class
		if ($validated['class'])
		{			
			$resource->class()->dissociate($validated['class'])->save();
		}
	}

}
