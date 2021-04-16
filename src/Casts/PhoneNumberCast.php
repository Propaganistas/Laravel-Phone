<?php

namespace Propaganistas\LaravelPhone\Casts;

use Illuminate\Contracts\Database\Eloquent\CastsAttributes;
use Illuminate\Contracts\Database\Eloquent\SerializesCastableAttributes;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;

abstract class PhoneNumberCast implements CastsAttributes, SerializesCastableAttributes
{
    /**
     * @var array
     */
    protected $parameters = [];

    /**
     * @param  mixed  $parameters
     */
    public function __construct($parameters = [])
    {
        $this->parameters = is_array($parameters) ? $parameters : func_get_args();
    }

    /**
     * @param string $key
     * @param  array  $attributes
     * @return array
     */
    protected function getPossibleCountries($key, array $attributes)
    {
        $parameters = $this->parameters;

        // Discover if an attribute was provided. If not, default to _country.
        $inputField = Collection::make($parameters)
            ->intersect(array_keys(Arr::dot($attributes)))
            ->first() ?: "${key}_country";

        // Attempt to retrieve the field's value.
        if ($inputCountry = Arr::get($attributes, $inputField)) {
            $parameters[] = $inputCountry;
        }

        return $parameters;
    }
}