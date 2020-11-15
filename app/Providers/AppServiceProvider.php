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
	}
}
