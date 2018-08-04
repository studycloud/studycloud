<?php

use App\Resource;
use Illuminate\Http\Request;
use App\Http\Resources\ResourceResource;

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
Route::resource('topics', 'TopicController');

Route::get('admins/{userid}',
	function($user_id)
	{
		$user = App\User::find($user_id);
		// return $user;
		return view('admins', compact('user'));
	}
);

Auth::routes();
