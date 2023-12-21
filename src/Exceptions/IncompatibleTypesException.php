<?php

namespace Propaganistas\LaravelPhone\Exceptions;

class IncompatibleTypesException extends \Exception
{
    public static function invalid()
    {
        return new static('Cannot use type and notType at the same time.');
    }
}
