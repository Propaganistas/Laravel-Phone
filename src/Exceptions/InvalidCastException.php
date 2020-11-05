<?php

namespace Propaganistas\LaravelPhone\Exceptions;

class InvalidCastException extends \Exception
{
    /**
     * Ambiguous parameter static constructor.
     *
     * @param string $parameter
     * @return static
     */
    public static function invalid($model, $castKey)
    {
        $modelClass = get_class($model);

        return new static("Unable to cast $castKey because $modelClass doens't implement a \Propaganistas\LaravelPhone\Models\HasPhoneNumber interface.");
    }
}
