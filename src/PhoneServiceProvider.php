<?php namespace Propaganistas\LaravelPhone;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Traits\Macroable;
use Illuminate\Validation\Rule;
use libphonenumber\PhoneNumberUtil;
use Propaganistas\LaravelPhone\Rules;
use Propaganistas\LaravelPhone\Validation;
use ReflectionClass;

class PhoneServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap the application events.
     *
     * @return void
     */
    public function boot()
    {
        $this->registerValidator();

        $this->registerRule();
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

        $this->app->alias('libphonenumber', PhoneNumberUtil::class);
    }

    /**
     * Register the "phone" validator.
     */
    protected function registerValidator()
    {
        $extend = static::canUseDependentValidation() ? 'extendDependent' : 'extend';

        $this->app['validator']->{$extend}('phone', Validation\Phone::class . '@validate');
    }

    /**
     * Register the "phone" rule macro.
     */
    protected function registerRule()
    {
        if (class_exists('Illuminate\Validation\Rule') && class_uses(Rule::class, Macroable::class)) {
            Rule::macro('phone', function () {
                return new Rules\Phone;
            });
        }
    }

    /**
     * Determine whether we can register a dependent validator.
     *
     * @return bool
     */
    public static function canUseDependentValidation()
    {
        $validator = new ReflectionClass('\Illuminate\Validation\Factory');

        return $validator->hasMethod('extendDependent');
    }
}
