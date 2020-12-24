<?php namespace Propaganistas\LaravelPhone\Traits;

use Illuminate\Support\Arr;
use libphonenumber\PhoneNumberFormat;
use ReflectionClass;

trait ParsesFormats
{
    /**
     * Array of available phone formats.
     *
     * @var array
     */
    protected static $formats;

    /**
     * Determine whether the given format is valid.
     *
     * @param string $format
     * @return bool
     */
    public static function isValidFormat($format)
    {
        return ! is_null(static::parseFormat($format));
    }

    /**
     * Parse a phone format.
     *
     * @param int|string $format
     * @return int|null
     */
    protected static function parseFormat($format)
    {
        static::loadFormats();

        // If the format equals a constant's value, just return it.
        if (in_array($format, static::$formats, true)) {
            return $format;
        }

        // Otherwise we'll assume the format is the constant's name.
        return Arr::get(static::$formats, strtoupper($format));
    }

    /**
     * Load all available formats once.
     */
    private static function loadFormats()
    {
        if (! static::$formats) {
            static::$formats = with(new ReflectionClass(PhoneNumberFormat::class))->getConstants();
        }
    }
}