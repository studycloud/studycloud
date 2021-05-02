<?php

namespace App\Http\Controllers;

use App\ResourceUse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class ResourceUseController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return ResourceUse::select('id', 'name')->get();
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    // public function create()
    // {
    //     //
    // }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $this->authorize('create', ResourceUse::class);
		// first, validate the request
		$validated = $request->validate([
			'name' => 'string|required|max:255|unique:App\ResourceUse,name'
		]);
        // TODO: return some kind of custom error message if the resource use name already exists?

		// create a new ResourceUse using mass assignment to add the 'name' attribute
		$resource_use = (new ResourceUse)->fill($validated);
		$resource_use->author_id = Auth::id();
		$resource_use->save();
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    // public function show($id)
    // {
    //     //
    // }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    // public function edit($id)
    // {
    //     //
    // }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, ResourceUse $resource_use)
    {
        $this->authorize('update', $resource_use);
        $validated = $request->validate([
            'name' => 'string|required|max:255|unique:App\ResourceUse,name'
        ]);

        $resource_use->fill($validated);
        $resource_use->author_id = Auth::id();
        $resource_use->save();
    }

    /**
     * Remove the specified resource use from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy(ResourceUse $resource_use)
    {
        $this->authorize('delete', $resource_use);
        // check that the resource can be deleted
        Validator::make([
            'resources_count' => $resource_use->resources()->count()
        ], [
            'resources_count' => 'integer|max:0'
        ], [
            'resources_count.max' => 'You must remove this resource_use from all of the resources it belongs to before deleting it.'
        ])->validate();
        // delete the resource
        $resource_use->delete();
    }
}
