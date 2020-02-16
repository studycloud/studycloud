<?php

namespace App\Http\Controllers;

use App\Notice;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

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

		// create a new Notice using mass assignment to add the 'name' attribute
		$notice = (new Notice)->fill($validated);
		$notice->author_id = Auth::id();
		// check that the parent attribute is not 0
		// otherwise, don't set the parent attribute, since it will default to NULL
		if ($validated['parent'])
		{
			$notice->parent()->associate($validated['parent']);
		}
		$notice->save();
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Notice  $notice
     * @return \Illuminate\Http\Response
     */
    // public function update(Request $request, Notice $notice)
    // {
    //     //
    // }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Notice  $notice
     * @return \Illuminate\Http\Response
     */
    public function destroy(Notice $notice)
    {
        // check that the author of this notice is not null
		// a null author indicates that the notice is not editable by the average user
		// TODO: convert this check to an auth rule
		// Validator::make($notice->toArray(),
        //     [
        //         'author_id' => 'required'
        //     ],
        //     [
        //         'author_id.required' => "This notice was created by the administrators. You cannot edit it."
        //     ]
        // )->validate();

        // TODO: make custom validation logic for the stuff below?
        // before deleting the notice, make sure it doesn't have any noticees attached underneath it
        // also make sure it doesn't have any resources attached to it
        // Validator::make([
        //     'children_count' => $notice->children()->count(),
        // ], [
        //     'children_count' => 'integer|max:0'
        // ], [
        //     'children_count.max' => 'You cannot delete a notice that has children.'
        // ])->validate();
        // actually delete the notice

        //$this->authorize('delete', $notice);
		//delete the notice's child notices if they exist
		$notice->children->each(
			function ($child)
			{
				$child->delete();
			}
		);

        $notice->delete();
    }
}
