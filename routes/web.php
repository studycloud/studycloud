<?php

use App\User;
use App\Topic;
use App\Resource;
use App\Academic_Class;
use App\Notice;
use Illuminate\Http\Request;
use App\Http\Middleware\CheckStatus;
use App\Http\Middleware\CheckAdminStatus;
use App\Http\Resources\ClassResource;
use App\Http\Resources\TopicResource;
use App\Http\Resources\ResourceResource;
use App\Http\Controllers\ClassController;
use App\Http\Controllers\NoticeController;

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
		// we don't need to check whether it exists. the checkstatus attribute will do that for us
		return new ResourceResource(Resource::findOrFail($request->input('id')));
	}
)->middleware(CheckStatus::class.':'.Resource::class)->name('resources.json');
Route::resource('resources', 'ResourceController', ['except' => 
	'index'
]);
Route::patch('/resources/attach/{resource}', 'ResourceController@attach')->name('resources.attach');
Route::patch('/resources/detach/{resource}', 'ResourceController@detach')->name('resources.detach');

Route::get('data/topic_tree', 'GetTree')->name('tree.topic');
Route::get('data/topic',
	function(Request $request)
	{
		return new TopicResource(Topic::findOrFail($request->input('id')));
	}
)->name('topics.json');
Route::resource('topics', 'TopicController');

Route::get('data/class_tree', 'GetTree')->name('tree.class');
Route::get('data/class',
	function(Request $request)
	{
		return new ClassResource(Academic_Class::findOrFail($request->input('id')));
	}
)->name('topics.json');
Route::resource('classes', 'ClassController');
Route::patch('/classes/attach/{class?}',
	function (Request $request, $class = null)
	{
		$class = $class == 0 || is_null($class) ? null : Academic_Class::findOrFail($class);
		return (new ClassController)->attach($request, $class);
	}
)->name('resources.attach');

Route::get('admin',
	function()
	{
		//$user = User::findOrFail($user_id);
		// return $user;
		return view('admin');
	}
);

if (App::environment('local'))
{
	Auth::routes();
}
else
{
	// authentication routes
	$this->post('logout', 'Auth\LoginController@logout')->name('logout');
}

// you can enable other providers by adding them in the routes' regex constraints
Route::get('login/{provider}', 'Auth\LoginController@redirectToProvider')->where('provider', '^(google)$')->name('login.oauth');
Route::get('login/{provider}/callback', 'Auth\LoginController@handleProviderCallback')->where('provider', '^(google)$');

Route::resource('notices', 'NoticeController', ['only' => [
	'index', 'store', 'destroy'
]]);

Route::resource('resource_uses', 'ResourceUseController', ['only' => [
	'index', 'store', 'update', 'destroy'
]])->parameters([
	'resource_uses' => 'resource_use'
]);
