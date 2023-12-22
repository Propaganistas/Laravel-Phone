<?php

namespace Propaganistas\LaravelPhone\Concerns;

use Illuminate\Support\Collection;
use libphonenumber\PhoneNumberUtil;

class PhoneNumberCountry
{
    public static function all(): array
    {
        return array_map('strtoupper', PhoneNumberUtil::getInstance()->getSupportedRegions());
    }

    public static function isValid($code): bool
    {
        return ! is_null($code) && in_array(strtoupper($code), static::all());
    }

    public static function sanitize($countries): string|array|null
    {
        $sanitized = Collection::make(is_array($countries) ? $countries : [$countries])
            ->filter(function ($value) {
                return static::isValid($value);
            })->map(function ($value) {
                return strtoupper($value);
            })->unique();

        return is_array($countries) ? $sanitized->toArray() : $sanitized->first();
    }
}
