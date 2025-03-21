<?php

namespace Propaganistas\LaravelPhone\Concerns;

use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use libphonenumber\PhoneNumberFormat as libPhoneNumberFormat;
use ReflectionClass;

/**
 * @internal
 */
class PhoneNumberFormat
{
    public static function all(): array
    {
        if (enum_exists(libPhoneNumberFormat::class)) {
            return Collection::make(libPhoneNumberFormat::cases())->mapWithKeys(function (libPhoneNumberFormat $format) {
                return [$format->name => $format->value];
            })->all();
        }

        return (new ReflectionClass(libPhoneNumberFormat::class))->getConstants();
    }

    public static function isValid($format): bool
    {
        if (enum_exists(libPhoneNumberFormat::class) && $format instanceof libPhoneNumberFormat) {
            $format = $format->value;
        }

        return ! is_null($format) && in_array($format, static::all(), true);
    }

    public static function isValidName($format): bool
    {
        $formats = array_map('strtoupper', array_keys(static::all()));

        return ! is_null($format) && in_array(strtoupper($format), $formats, true);
    }

    public static function getHumanReadableName($format): ?string
    {
        if (enum_exists(libPhoneNumberFormat::class) && $format instanceof libPhoneNumberFormat) {
            $format = $format->value;
        }

        $name = array_search($format, static::all(), true);

        return $name ? strtolower($name) : null;
    }

    public static function sanitize($formats): int|array|null
    {
        $sanitized = Collection::make(is_array($formats) ? $formats : [$formats])
            ->map(function ($format) {
                if (enum_exists(libPhoneNumberFormat::class) && $format instanceof libPhoneNumberFormat) {
                    $format = $format->value;
                }

                // If the format equals a constant's name, return its value.
                // Otherwise just return the value.
                return Arr::get(static::all(), strtoupper($format), $format);
            })
            ->filter(function ($format) {
                return static::isValid($format);
            })->unique();

        return is_array($formats) ? $sanitized->toArray() : $sanitized->first();
    }
}
