<?php

namespace Propaganistas\LaravelPhone\Concerns;

use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use libphonenumber\PhoneNumberType as libPhoneNumberType;
use ReflectionClass;

class PhoneNumberType
{
    public static function all(): array
    {
        return (new ReflectionClass(libPhoneNumberType::class))->getConstants();
    }

    public static function isValid($type): bool
    {
        return ! is_null($type) && in_array($type, static::all(), true);
    }

    public static function isValidName($type): bool
    {
        $types = array_map('strtoupper', array_keys(static::all()));

        return ! is_null($type) && in_array(strtoupper($type), $types, true);
    }

    public static function getHumanReadableName($type): string|null
    {
        $name = array_search($type, static::all(), true);

        return $name ? strtolower($name) : null;
    }

    public static function sanitize($types): int|array|null
    {
        $sanitized = Collection::make(is_array($types) ? $types : [$types])
            ->map(function ($format) {
                // If the type equals a constant's name, return its value.
                // Otherwise just return the value.
                return Arr::get(static::all(), strtoupper($format), $format);
            })
            ->filter(function ($format) {
                return static::isValid($format);
            })->unique();

        return is_array($types) ? $sanitized->toArray() : $sanitized->first();
    }
}
