<?php

namespace App\Http\Controllers;

use App\Resource;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Database\Eloquent\Collection;




/*

TODO:
Think about each of the functions we need in each of our controllers. 

Implement the code using the models.
We want to be able to crate a resource that can be manipulated at will. What do we want in this resource?

Use functions 



*/



class ResourceController extends Controller
{
	/**
	 * Display a listing of the resource.
	 *
	 * @return \Illuminate\Http\Response
	 */
	public function index()
	{
		//
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

		$newResource->save();
	}

	/**
	 * Display the specified resource.
	 *
	 * @param  \App\Resource  $resource
	 * @return \Illuminate\Http\Response
	 */
	public function show(Resource $resource)
	{
		return view('resource', ['resource' => $resource]);
	}

	/**
	 * Show the form for editing the specified resource.
	 *
	 * @param  \App\Resource  $resource
	 * @return \Illuminate\Http\Response
	 */
	public function edit(Resource $resource)
	{
		//
	}

	/**
	 * Update the specified resource in storage.
	 *
	 * @param  \Illuminate\Http\Request  $request
	 * @param  \App\Resource  $resource
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
	 *Moves the resource under a specific named topic
	 *
	 * @param \App\Resource $resource
	 * @param \App\Topic $topic
	 * @return \Illuminate\Http\Response
	 *
	 *
	 */
	public function move(Resource $resource, Topic $topic)
	{
		# do stuff
	}


	/**
	 * Remove the specified resource from storage.
	 *
	 * @param  \App\Resource  $resource
	 * @return \Illuminate\Http\Response
	 */
	public function destroy(Resource $resource)
	{
		$resource->delete();
	}
}
