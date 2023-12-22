<?php

namespace Propaganistas\LaravelPhone\Casts;

use Illuminate\Contracts\Database\Eloquent\CastsAttributes;
use Illuminate\Contracts\Database\Eloquent\SerializesCastableAttributes;
use Illuminate\Support\Arr;
use Propaganistas\LaravelPhone\Concerns\PhoneNumberCountry;

abstract class PhoneNumberCast implements CastsAttributes, SerializesCastableAttributes
{
    protected array $parameters;

    public function __construct()
    {
        $this->parameters = func_get_args();
    }

    protected function getPossibleCountries($key, array $attributes): array
    {
        $parameters = array_map(function ($parameter) use ($attributes) {
            if ($value = Arr::get($attributes, $parameter)) {
                return $value;
            }

            return $parameter;
        }, [...$this->parameters, $key.'_country']);

        return array_filter($parameters, function ($parameter) {
            return PhoneNumberCountry::isValid($parameter);
        });
    }
}
