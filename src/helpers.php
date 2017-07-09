<?php

use Illuminate\Support\Facades\App;
use libphonenumber\PhoneNumberFormat;
use Illuminate\Support\Facades\Validator;

if (! function_exists('phone')) {
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
        
        $countries = isset($arguments[1]) ? (is_array($arguments[1]) ? $arguments[1] : [$arguments[1]]) : [];
        
        $format = isset($arguments[2]) ? $arguments[2] : PhoneNumberFormat::INTERNATIONAL;

        $validator = null;

        for($i = 0; $i < sizeof($countries); $i++)
        {
            if($countries[$i] === null)
            {
                return $lib->format(
                    $lib->parse($phone, App::getLocale()),
                    $format
                );
            }

            $validator = Validator::make(['phone' => $phone], [
                'phone' => 'required|phone:' . $countries[$i],
            ]);

            if (!$validator->fails()) {
                return $lib->format(
                    $lib->parse($phone, $countries[$i]),
                    $format
                );
            }
        }

        return $lib;
    }
}

if (! function_exists('phone_format')) {
    /**
     * Formats a phone number and country for display.
     *
     * @param string          $phone
     * @param string|string[] $countries
     * @param int|null        $format
     * @return string
     *
     * @deprecated 2.8.0
     */
    function phone_format($phone, $countries = null, $format = PhoneNumberFormat::INTERNATIONAL)
    {
        return phone($phone, $countries, $format);
    }
}
