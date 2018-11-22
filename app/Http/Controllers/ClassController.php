<?php

namespace App\Http\Controllers;

use App\Academic_Class;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ClassController extends Controller
{
    function __construct()
    {
        // verify that the user is signed in for all methods except index, show, and json
        $this->middleware('auth', ['except' => ['index', 'show']]);
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return view('classes');
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        // return a view for creating a new topic
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
            'parent' => 'int|required|exists:classes,id'
        ]);

        // create a new Academic_Class using mass assignment to add the 'name' attribute
        $class = (new Academic_Class)->fill($validated);
        $class->author_id = Auth::id();
        $class->save();
        $class->parents()->attach($validated['parent']);
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Academic_Class  $academic_Class
     * @return \Illuminate\Http\Response
     */
    public function show(Academic_Class $class)
    {
        // let the js handle parsing the URL to determine which topic to retrieve
        return view('classes');
    }

    /**
     * Show the form for editing the specified resource.
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
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Academic_Class  $class
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Academic_Class $class)
    {
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
     * Remove the specified resource from storage.
     *
     * @param  \App\Academic_Class  $class
     * @return \Illuminate\Http\Response
     */
    public function destroy(Academic_Class $class)
    {
        // before deleting the class, make sure it doesn't have any classes attached underneath it
        if ($class->resources()->get()->count() > 0)
        {
            abort(405, "You cannot delete a class that has children");
        }
        // also make sure it doesn't have any resources attached to it
        if ($class->children()->get()->count() > 0)
        {
            abort(405, "You cannot delete a class that has children");
        }
        // delete any parent relationships
        $class->parents->pluck('pivot')->each(
            function ($class_parent)
            {
                $class_parent->delete();
            }
        );
        // actually delete the class
        $class->delete();
    }
}
