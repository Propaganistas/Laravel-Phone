<?php namespace Propaganistas\LaravelPhone;

use Illuminate\Support\ServiceProvider;
use Illuminate\Validation\Factory;

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
		$this->package('propaganistas/laravel-phone');

		// Registering the validator extension with the validator factory.
		$this->app['validator']->resolver(function($translator, $data, $rules, $messages)
		{
			// Set custom validation error message.
			$messages['phone'] = $translator->get('laravel-phone::validation.phone');

			return new PhoneValidator($translator, $data, $rules, $messages);
		});
	}

	/**
	 * Register the service provider.
	 *
	 * @return void
	 */
	public function register() {}

	/**
	 * Get the services provided by the provider.
	 *
	 * @return array
	 */
	public function provides()
	{
		return array();
	}
}