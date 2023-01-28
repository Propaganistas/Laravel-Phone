<?php

namespace Propaganistas\LaravelPhone\Exceptions;

class CountryCodeException extends \Exception
{
    public static function invalid($country)
    {
        return new static('Invalid country code "'.$country.'".');
    }
}
