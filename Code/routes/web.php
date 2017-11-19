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
    return view('home');   	
});

Route::get('about', function(){
	return view('about');
});

Route::get('tree', function(){
	return view('tree');
});

Route::get('tree/data/topic/{topic_id?}/levels/{levels?}', 'TopicTreeController@show');

Route::get('admins/{userid}', function($user_id){
	$user = App\User::find($user_id);
	// return $user;
	return view('admins', compact('user'));
});