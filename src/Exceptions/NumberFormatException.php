<?php namespace Propaganistas\LaravelPhone\Exceptions;

use Exception;

class NumberFormatException extends Exception implements PhoneNumberException
{
    /**
     * Invalid number format static constructor.
     *
     * @param string $format
     * @return static
     */
    public static function invalid($format)
    {
        return new static('Invalid number format "' . $format . '".');
    }
}