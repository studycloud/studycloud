<?php

namespace App\Http\Controllers;

use App\Resource;
use Illuminate\Http\Request;




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
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
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
        $newResource = new Resource;
        $newResource->name = $request->name;
        $newResource->author_id = $request->author_id;
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
        return view('resource', ['resource' : $resource->name]    )
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
        $resource->author_id = $request->author_id;
        $resource->use_id = $request->use_id;
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Resource  $resource
     * @return \Illuminate\Http\Response
     */
    public function destroy(Resource $resource)
    {
        $deletedResource = $resource->id;
        $deletedResource->delete();

    }
}
