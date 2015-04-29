<?php namespace Propaganistas\LaravelPhone;

use Illuminate\Support\ServiceProvider;

class LaravelPhoneServiceProvider extends ServiceProvider
{
	/**
	 * Indicates if loading of the provider is deferred.
	 *
	 * @var bool
	 */
	protected $defer = false;

	/**
	 * Bootstrap the application events.
	 *
	 * @return void
	 */
	public function boot()
	{
		$this->app['validator']->extend('phone', 'Propaganistas\LaravelPhone\Validator@phone');
	}

	/**
	 * Register the service provider.
	 *
	 * @return void
	 */
	public function register() {}
}
