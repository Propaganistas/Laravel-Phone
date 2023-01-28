<?php

namespace Propaganistas\LaravelPhone\Exceptions;

class NumberFormatException extends \Exception
{
    public static function invalid($format)
    {
        return new static('Invalid number format "'.$format.'".');
    }
}
