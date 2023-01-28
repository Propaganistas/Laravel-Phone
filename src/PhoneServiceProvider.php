<?php

namespace Propaganistas\LaravelPhone;

use Illuminate\Support\ServiceProvider;
use Illuminate\Validation\Factory;
use Illuminate\Validation\Rule;
use libphonenumber\PhoneNumberUtil;

class PhoneServiceProvider extends ServiceProvider
{
    public function register()
    {
        $this->registerLibraryBinding();
        $this->registerValidator();
    }

    public function registerLibraryBinding(): void
    {
        $this->app->singleton('libphonenumber', function ($app) {
            return PhoneNumberUtil::getInstance();
        });

        $this->app->alias('libphonenumber', PhoneNumberUtil::class);
    }

    public function registerValidator(): void
    {
        $this->callAfterResolving('validator', function (Factory $validator) {
            $validator->extendDependent('phone', function ($attribute, $value, array $parameters, $validator) {
                return (new Rules\Phone)
                    ->setValidator($validator)
                    ->setParameters($parameters)
                    ->passes($attribute, $value);
            });
        });

        Rule::macro('phone', function () {
            return new Rules\Phone;
        });
    }
}
