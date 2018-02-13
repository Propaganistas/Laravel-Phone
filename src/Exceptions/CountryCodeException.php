<?php namespace Propaganistas\LaravelPhone\Exceptions;

use Exception;

class CountryCodeException extends Exception implements PhoneNumberException
{
    /**
     * Invalid country code static constructor.
     *
     * @param string $country
     * @return static
     */
    public static function invalid($country)
    {
        return new static('Invalid country code "' . $country . '".');
    }
}
