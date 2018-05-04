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

Route::get('/home', 'HomeController@index')->name('home');

Route::redirect('/', '/home', 301);

Route::get('about', function(){
	return view('about');
})->name('about');

// TEMPORARY FOR TESTING
Route::get('resource', function(){
	return view('resource');
});

Route::get('topics', function(){
	return view('topics');
});

Route::get('resource/{resource}', 'ResourceController@show');

Route::get('tree/data', 'TopicTreeController');

Route::get('admins/{userid}', function($user_id){
	$user = App\User::find($user_id);
	// return $user;
	return view('admins', compact('user'));
});

Auth::routes();
