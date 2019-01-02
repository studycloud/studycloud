<?php

namespace App\Providers;

use App\Http\Resources\ClassResource;
use App\Http\Resources\TopicResource;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
	/**
	 * Bootstrap any application services.
	 *
	 * @return void
	 */
	public function boot()
	{
		// disable wrapping of the outermost resource with a 'data' attribute
		TopicResource::withoutWrapping();
		ClassResource::withoutWrapping();
	}

	/**
	 * Register any application services.
	 *
	 * @return void
	 */
	public function register()
	{
		//
	}
}
