<?php

namespace App\Http\Controllers;

use App\Notice;
use Illuminate\Http\Request;

class NoticeController extends Controller
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
     * Store a newly created resource in storage.
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
			'link' => 'string|required|max:255',
			'parent' => [
				'integer',
				'nullable',
				Rule::in(
					Notice::pluck('id')->toArray()
				)
            ], 
            'priority' => 'integer|max:255',
            'deadline' => 'date|after:now|nullable',
            'description' => 'string|required'
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
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Notice  $notice
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Notice $notice)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Notice  $notice
     * @return \Illuminate\Http\Response
     */
    public function destroy(Notice $notice)
    {
        //
    }
}
