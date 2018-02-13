<?php namespace Propaganistas\LaravelPhone\Exceptions;

use Exception;
use libphonenumber\NumberParseException as libNumberParseException;

class NumberParseException extends Exception implements PhoneNumberException
{
    /**
     * Country specification required static constructor.
     *
     * @param string $number
     * @return static
     */
    public static function countryRequired($number)
    {
        return new static(
            libNumberParseException::INVALID_COUNTRY_CODE,
            'Country specification for number "' . $number . '" required.'
        );
    }
}