<?php

function phone_format($phone, $country, $format = null) {
    $phoneUtil = \libphonenumber\PhoneNumberUtil::getInstance();
    $phoneProto = $phoneUtil->parse($phone, $country);
    $format = is_null($format) ? \libphonenumber\PhoneNumberFormat::INTERNATIONAL : $format;
    return $phoneUtil->format($phoneProto, $format);
}
