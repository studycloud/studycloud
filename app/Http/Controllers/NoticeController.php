<?php

namespace App\Http\Controllers;

use App\Notice;
use App\User;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use App\Http\Middleware\CheckAdminStatus;

class NoticeController extends Controller
{
    function __construct()
	{
		// verify that the user is signed in for all methods
		$this->middleware('auth');
		// check that the user is an admin before letting them access notices
		$this->middleware(CheckAdminStatus::class);
    }
    
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $notices = Notice::all();
        return $notices->map(function($notice, $notice_id) {
            return view('notice', [
                'author' => $notice->author, 
                'description' => $notice->description,
                'deadline' => $notice->deadline,
                'link' => $notice->link,
                'priority' => $notice->priority,
                'id' => $notice_id,
                'owner' => $notice->owner
            ])->render();
        })->implode("");
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
            'description' => 'string|required',
            'status' => [
                'integer',
                'nullable'
		    ]]);

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
    public function update(Request $request, Notice $notice)
    {
        // first, validate the request
		$validated = $request->validate([
            'claimed' => 'boolean'
            ]);
            
        if ($validated['claimed'])
        {
            $notice->status = Auth::user()->id;
        }
        else
        {
            $notice->status = null;
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Notice  $notice
     * @return \Illuminate\Http\Response
     */
    public function destroy(Notice $notice)
    {
        // $this->authorize('delete', $notice);
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
