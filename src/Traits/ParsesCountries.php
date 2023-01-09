<?php

namespace Propaganistas\LaravelPhone\Traits;

use Illuminate\Support\Collection;
use libphonenumber\PhoneNumberUtil;

trait ParsesCountries
{
    /**
     * Determine whether the given country code is valid.
     *
     * @param string $country
     * @return bool
     */
    public static function isValidCountryCode($country)
    {
        return in_array($country, PhoneNumberUtil::getInstance()->getSupportedRegions());
    }

    /**
     * Parse the provided phone countries to a valid array.
     *
     * @param string|array $countries
     * @return array
     */
    protected function parseCountries($countries)
    {
        return Collection::make(is_array($countries) ? $countries : func_get_args())
            ->reject(function ($value) {
                /** @phpstan-ignore-next-line */
                return is_null($value);
            })
            ->map(function ($country) {
                return strtoupper($country);
            })
            ->filter(function ($value) {
                return static::isValidCountryCode($value);
            })->toArray();
    }
}
