<?php namespace Propaganistas\LaravelPhone\Exceptions;

class NumberFormatException extends \Exception
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