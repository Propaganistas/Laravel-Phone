<?php

use libphonenumber\PhoneNumberFormat;
use Propaganistas\LaravelPhone\PhoneNumber;

if (! function_exists('phone')) {
    /**
     * @param  array<string>|string|null  $country
     * @return ($format is null ? \Propaganistas\LaravelPhone\PhoneNumber : string)
     */
    function phone(string $number, array|string|null $country = null, PhoneNumberFormat|string|null $format = null): PhoneNumber|string
    {
        $phone = new PhoneNumber($number, $country);

        return is_null($format) ? $phone : $phone->format($format);
    }
}
