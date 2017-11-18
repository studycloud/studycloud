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

Route::get('tree/connections', function(){
	return App\TopicParent::all();
});

Route::get('tree/data', function(){
	return App\Topic::all();
});

Route::get('tree/data/{topic_id}/{levels?}', function($topic_id, $levels = 0){
	return App\Topic::find($topic_id)->descendants($levels);
});

Route::get('admins/{userid}', function($user_id){
	$user = App\User::find($user_id);
	// return $user;
	return view('admins', compact('user'));
});

Auth::routes();

Route::get('/home', 'HomeController@index')->name('home');
