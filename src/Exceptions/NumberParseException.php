<?php

namespace Propaganistas\LaravelPhone\Exceptions;

use Illuminate\Support\Str;
use libphonenumber\NumberParseException as libNumberParseException;

class NumberParseException extends libNumberParseException
{
    /**
     * @var string
     */
    protected $number;

    /**
     * @var array
     */
    protected $countries = [];

    /**
     * Country specification required static constructor.
     *
     * @param string $number
     * @return static
     */
    public static function countryRequired($number)
    {
        $exception = new static(
            libNumberParseException::INVALID_COUNTRY_CODE,
            'Number requires a country to be specified.'
        );

        $exception->number = $number;

        return $exception;
    }

    /**
     * Country mismatch static constructor.
     *
     * @param string $number
     * @param string|array $countries
     * @return static
     */
    public static function countryMismatch($number, $countries)
    {
        $countries = array_filter(is_array($countries) ? $countries : [$countries]);

        $exception = new static(
            libNumberParseException::INVALID_COUNTRY_CODE,
            'Number does not match the provided '. Str::plural('country', count($countries)).'.'
        );

        $exception->number = $number;
        $exception->countries = $countries;

        return $exception;
    }

    /**
     * @return string
     */
    public function getNumber()
    {
        return $this->number;
    }

    /**
     * @return array
     */
    public function getCountries()
    {
        return $this->countries;
    }
}