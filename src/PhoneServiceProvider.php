<?php

namespace Propaganistas\LaravelPhone;

use Illuminate\Support\ServiceProvider;
use Illuminate\Validation\Factory;
use Illuminate\Validation\InvokableValidationRule;
use Illuminate\Validation\Rule;
use Propaganistas\LaravelPhone\Rules\Phone;

class PhoneServiceProvider extends ServiceProvider
{
    public function register()
    {
        $this->callAfterResolving('validator', function (Factory $validator) {
            $validator->extendDependent('phone', function ($attribute, $value, array $parameters, $validator) {
                $rule = (new Phone)
                    ->setData($validator->getData())
                    ->setParameters($parameters);

                return InvokableValidationRule::make($rule)
                    ->setValidator($validator)
                    ->passes($attribute, $value);
            });
        });

        Rule::macro('phone', function () {
            return new Rules\Phone;
        });
    }
}
