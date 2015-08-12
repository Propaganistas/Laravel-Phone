<?php namespace Propaganistas\LaravelPhone;

use Illuminate\Support\ServiceProvider;
use libphonenumber\PhoneNumberUtil;

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
		$this->app['validator']->extend('phone', 'Propaganistas\LaravelPhone\PhoneValidator@validatePhone');
	}

	/**
	 * Register the service provider.
	 *
	 * @return void
	 */
	public function register() {
		// Make libphonenumber available in the IoC.
		$this->app->singleton(PhoneNumberUtil::class, function ($app) {
			return PhoneNumberUtil::getInstance();
		});
		$this->app->alias(PhoneNumberUtil::class, 'libphonenumber');
	}
}
