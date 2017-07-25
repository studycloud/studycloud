<?php

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/', function () {
    return view('welcome', compact('people'));   	
});

Route::get('about', function(){
	return view('about');
});

Route::get('tree', function(){
	return view('tree');
});

Route::get('tree/connections', function(){
	return App\TopicParent::all();
});

Route::get('tree/data', function(){
	return App\Topic::all();
});

Route::get('admins/{userid}', function($userid){
	$user = App\User::find($userid);
	// return $user;
	return view('admins', compact('user'));
});