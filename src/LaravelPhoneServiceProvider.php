<?php namespace Propaganistas\LaravelPhone;

use Illuminate\Support\ServiceProvider;
use libphonenumber\PhoneNumberUtil;

class LaravelPhoneServiceProvider extends ServiceProvider
{

	/**
	 * Bootstrap the application events.
	 *
	 * @return void
	 */
	public function boot()
	{
		$this->app['validator']->extend('phone', 'Propaganistas\LaravelPhone\PhoneValidator@validatePhone');
	}

	/**
	 * Register the service provider.
	 *
	 * @return void
	 */
	public function register()
	{
		// Make libphonenumber available in the application container.
		$this->app->singleton('libphonenumber', function($app) {
			return PhoneNumberUtil::getInstance();
		});
		$this->app->alias('libphonenumber', 'libphonenumber\PhoneNumberUtil');
	}
}
