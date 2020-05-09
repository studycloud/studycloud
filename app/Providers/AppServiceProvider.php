<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Http\Resources\Json\Resource;

class AppServiceProvider extends ServiceProvider
{
	/**
	 * Bootstrap any application services.
	 *
	 * @return void
	 */
	public function boot()
	{
		// disable wrapping of arrays returned by Resource classes with a data attribute
		Resource::withoutWrapping();
	}

	/**
	 * Register any application services.
	 *
	 * @return void
	 */
	public function register()
	{
		// register any global helper functions
		$file = app_path('Helpers/Helper.php');
		if (file_exists($file)) {
			require_once($file);
		}

		// register the import course request class
		// So that when a controller asks for a class that implements the
		// ImportCourseRequest abstract class, Laravel will be able to resolve
		// it appropriately.
		// When you have other schools and other CourseImportAPIs, you can
		// turn this into a conditional if/elseif/elseif etc to determine the
		// correct CourseImportAPI to use
		$this->app->bind('App\Http\Requests\CourseImportRequest', 'App\Http\Requests\CourseImportAPIs\HyperscheduleAPI');
	}
}
