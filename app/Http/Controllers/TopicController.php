<?php

namespace App\Http\Controllers;

use App\Topic;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class TopicController extends Controller
{
	function __construct()
	{
		// verify that the user is signed in for all methods except index, show, and json
		$this->middleware('auth', ['except' => ['index', 'show']]);
	}

	/**
	 * Display a listing of the topic.
	 *
	 * @return \Illuminate\Http\Response
	 */
	public function index()
	{
		return view('tree', ['type'=>'topic']);
	}

	/**
	 * Show the form for creating a new topic.
	 *
	 * @return \Illuminate\Http\Response
	 */
	public function create()
	{
		// return a view for creating a new topic
	}

	/**
	 * Store a newly created topic in storage.
	 *
	 * @param  \Illuminate\Http\Request  $request
	 * @return \Illuminate\Http\Response
	 */
	public function store(Request $request)
	{
		// first, validate the request
		$validated = $request->validate([
			'name' => 'string|required|max:255'
		]);

		// create a new Topic using mass assignment to add the 'name' attribute
		$topic = (new Topic)->fill($validated);
		$topic->author_id = Auth::id();
		$topic->save();
	}

	/**
	 * Display the specified topic.
	 *
	 * @param  \App\Topic  $topic
	 * @return \Illuminate\Http\Response
	 */
	public function show(Topic $topic)
	{
		// let the js handle parsing the URL to determine which topic to retrieve
		return view('tree', ['type'=>'tree']);
	}

	/**
	 * Show the form for editing the specified topic.
	 *
	 * @param  \App\Topic  $topic
	 * @return \Illuminate\Http\Response
	 */
	public function edit(Topic $topic)
	{
		// return a view for editing a topic
		// perhaps this functionality should be embedded in the topic tree, though?
	}

	/**
	 * Update the specified topic in storage.
	 *
	 * @param  \Illuminate\Http\Request  $request
	 * @param  \App\Topic  $topic
	 * @return \Illuminate\Http\Response
	 */
	public function update(Request $request, Topic $topic)
	{
		// first, validate the request
		// note that we make the 'name' attribute required because there aren't any other attributes to validate
		$validated = $request->validate([
			'name' => 'string|required|max:255'
		]);

		// create a new Topic using mass assignment to add the 'name' attribute
		$topic = $topic->fill($validated);
		$topic->author_id = Auth::id();
		$topic->save();
	}

	/**
	 * Remove the specified topic from storage.
	 *
	 * @param  \App\Topic  $topic
	 * @return \Illuminate\Http\Response
	 */
	public function destroy(Topic $topic)
	{
		$topic->delete();
	}

}
