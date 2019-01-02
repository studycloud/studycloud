<?php

use App\User;
use App\Topic;
use App\Resource;
use App\Academic_Class;
use Illuminate\Http\Request;
use App\Http\Resources\ClassResource;
use App\Http\Resources\TopicResource;
use App\Http\Resources\ResourceResource;
use App\Http\Controllers\ClassController;

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

Route::get('about',
	function()
	{
		return view('about');
	}
)->name('about');

Route::get('data/resource',
	function(Request $request)
	{
		return new ResourceResource(Resource::find($request->query('id')));
	}
)->name('resources.json');
Route::resource('resources', 'ResourceController', ['except' => 
	'index', 'edit'
]);

Route::get('data/topic_tree', 'GetTopicTree');
Route::get('data/topic',
	function(Request $request)
	{
		return new TopicResource(Topic::find($request->query('id')));
	}
)->name('topics.json');
Route::resource('topics', 'TopicController');

// Route::get('data/class_tree', 'GetTopicTree');
Route::get('data/class',
	function(Request $request)
	{
		return new ClassResource(Academic_Class::find($request->query('id')));
	}
)->name('topics.json');
Route::resource('classes', 'ClassController');
Route::patch('/classes/attach/{class}',
	function (Request $request, $class)
	{
		$class = $class == 0 ? null : Academic_Class::find($class);
		return (new ClassController)->attach($request, $class);
	}
)->name('resources.attach');

Route::get('admins/{userid}',
	function($user_id)
	{
		$user = User::find($user_id);
		// return $user;
		return view('admins', compact('user'));
	}
);

Auth::routes();
