<?php

namespace Propaganistas\LaravelPhone\Exceptions;

use Illuminate\Support\Collection;

class InvalidParameterException extends \Exception
{
    /**
     * Ambiguous parameter static constructor.
     *
     * @param string $parameter
     * @return static
     */
    public static function ambiguous($parameter)
    {
        return new static('Ambiguous phone validation parameter: "' . $parameter . '". This parameter is recognized as an input field and as a phone type. Please rename the input field.');
    }
}
