<?php

namespace Propaganistas\LaravelPhone\Traits;

use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use libphonenumber\PhoneNumberType;
use ReflectionClass;

trait ParsesTypes
{
    /**
     * Array of available phone types.
     *
     * @var array
     */
    protected static $resolvedTypes;

    /**
     * Determine whether the given type is valid.
     *
     * @param int|string $type
     * @return bool
     */
    public static function isValidType($type)
    {
        return ! empty(static::parseTypes($type));
    }

    /**
     * Parse a phone type into constant's value.
     *
     * @param int|string|array $types
     * @return array
     */
    protected static function parseTypes($types)
    {
        self::loadTypes();

        return Collection::make(is_array($types) ? $types : func_get_args())
                         ->reject(function ($value) {
                             return is_null($value);
                         })
                         ->map(function ($type) {
                             // If the type equals a constant's value, just return it.
                             if (is_numeric($type) && in_array($type, static::$resolvedTypes)) {
                                 return (int) $type;
                             }

                             // Otherwise we'll assume the type is the constant's name.
                             return Arr::get(static::$resolvedTypes, strtoupper($type));
                         })
                         ->filter(function ($value) {
                             return is_numeric($value);
                         })->toArray();
    }

    /**
     * Parse a phone type into its string representation.
     *
     * @param int|string|array $types
     * @return array
     */
    protected static function parseTypesAsStrings($types)
    {
        self::loadTypes();

        return array_keys(
            array_intersect(
                static::$resolvedTypes,
                static::parseTypes($types)
            )
        );
    }

    /**
     * Load all available formats once.
     */
    private static function loadTypes()
    {
        if (! static::$resolvedTypes) {
            static::$resolvedTypes = with(new ReflectionClass(PhoneNumberType::class))->getConstants();
        }
    }
}
