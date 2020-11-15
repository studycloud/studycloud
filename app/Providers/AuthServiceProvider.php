<?php

namespace App\Providers;

use App\Resource;
use App\ResourceUse;
use App\Policies\ResourcePolicy;
use App\Policies\ResourceUsePolicy;
use Illuminate\Support\Facades\Gate;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;

class AuthServiceProvider extends ServiceProvider
{
	/**
	 * The policy mappings for the application.
	 *
	 * @var array
	 */
	protected $policies = [
		'App\Model' => 'App\Policies\ModelPolicy',
		Resource::class => ResourcePolicy::class,
		ResourceUse::class => ResourceUsePolicy::class
	];

	/**
	 * Register any authentication / authorization services.
	 *
	 * @return void
	 */
	public function boot()
	{
		$this->registerPolicies();

		//
	}
}
