<?php namespace Propaganistas\LaravelPhone;

use Illuminate\Foundation\Application;
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
        $extend = version_compare(Application::VERSION, '5.4.18', '>=')
            ? 'extendDependent'
            : 'extend';

        $this->app['validator']->{$extend}('phone', 'Propaganistas\LaravelPhone\PhoneValidator@validatePhone');
    }

    /**
     * Register the service provider.
     *
     * @return void
     */
    public function register()
    {
        $this->app->singleton('libphonenumber', function ($app) {
            return PhoneNumberUtil::getInstance();
        });

        $this->app->alias('libphonenumber', 'libphonenumber\PhoneNumberUtil');
    }
}
