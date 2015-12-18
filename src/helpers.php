<?php

use Illuminate\Support\Facades\App;
use libphonenumber\PhoneNumberFormat;

function phone_format($phone, $country, $format = null) {
    $lib = App::make('libphonenumber');
    $phoneNumber = $lib->parse($phone, $country);
    $format = is_null($format) ? PhoneNumberFormat::INTERNATIONAL : $format;
    return $lib->format($phoneNumber, $format);
}
