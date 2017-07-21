<?php namespace Propaganistas\LaravelPhone\Exceptions;

use Illuminate\Support\Collection;

class InvalidParameterException extends \Exception
{
    /**
     * Invalid parameters static constructor.
     *
     * @param array|Collection $parameters
     * @return static
     */
    public static function parameters($parameters)
    {
        $parameters = Collection::make($parameters);

        return new static('Invalid phone validation parameters: "' . $parameters->implode(',') . '".');
    }
}