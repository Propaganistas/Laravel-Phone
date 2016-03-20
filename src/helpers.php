<?php

use Illuminate\Support\Facades\App;
use libphonenumber\PhoneNumberFormat;

if (!function_exists('phone_format')) {
	/**
	 * Formats a phone number and country for display.
	 *
	 * @param string   $phone
	 * @param string   $country
	 * @param int|null $format
	 * @return string
	 */
	function phone_format($phone, $country, $format = PhoneNumberFormat::INTERNATIONAL)
	{
		$lib = App::make('libphonenumber');
		$phoneNumber = $lib->parse($phone, $country);

		return $lib->format($phoneNumber, $format);
	}
}
