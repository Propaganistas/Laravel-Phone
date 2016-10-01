<?php

use Illuminate\Support\Facades\App;
use libphonenumber\PhoneNumberFormat;

if (! function_exists('phone_format')) {
    /**
     * Get the PhoneNumberUtil or format a phone number for display.
     *
     * @return \libphonenumber\PhoneNumberUtil|string
     */
    function phone()
    {
        $lib = App::make('libphonenumber');

        if (! $arguments = func_get_args()) {
            return $lib;
        }

        $phone = $arguments[0];
        $country = isset($arguments[1]) ? $arguments[1] : App::getLocale();
        $format = isset($arguments[2]) ? $arguments[2] : PhoneNumberFormat::INTERNATIONAL;

        return $lib->format(
            $lib->parse($phone, $country),
            $format
        );
    }
}

if (! function_exists('phone_format')) {
    /**
     * Formats a phone number and country for display.
     *
     * @param string   $phone
     * @param string   $country
     * @param int|null $format
     * @return string
     *
     * @deprecated 2.8.0
     */
    function phone_format($phone, $country = null, $format = PhoneNumberFormat::INTERNATIONAL)
    {
        return phone($phone, $country, $format);
    }
}
