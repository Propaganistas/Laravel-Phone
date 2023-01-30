<?php

use Propaganistas\LaravelPhone\PhoneNumber;

if (! function_exists('phone')) {
    function phone(?string $number, $country = [], $format = null)
    {
        $phone = new PhoneNumber($number, $country);

        if (! is_null($format)) {
            return $phone->format($format);
        }

        return $phone;
    }
}
