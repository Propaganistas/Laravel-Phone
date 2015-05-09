<?php namespace Propaganistas\LaravelPhone;

use libphonenumber\PhoneNumberUtil;
use libphonenumber\NumberParseException;
use libphonenumber\PhoneNumberType;

class Validator
{
	/**
	 * Validates a phone number field using libphonenumber.
	 */
	public function phone($attribute, $value, $parameters, $validator)
	{
		$data = $validator->getData();
		$countries = array();
		$types = array();

		// Explode the parameters in appropriate arrays.
		foreach ($parameters as $parameter) {
			if ($this->phone_country($parameter)) {
				// Extract countries.
				$countries[] = $parameter;
			}
			else {
				// Check if we should only allow specific phone number types.
				switch ($parameter) {
					case 'landline':
						$types[] = PhoneNumberType::FIXED_LINE;
						$types[] = PhoneNumberType::FIXED_LINE_OR_MOBILE;
						break;
					case 'mobile':
						$types[] = PhoneNumberType::MOBILE;
						$types[] = PhoneNumberType::FIXED_LINE_OR_MOBILE;
						break;
					case 'voip':
						$types[] = PhoneNumberType::VOIP;
						break;
					case 'pager':
						$types[] = PhoneNumberType::PAGER;
						break;
					case 'uan':
						$types[] = PhoneNumberType::UAN;
						break;
					case 'emergency':
						$types[] = PhoneNumberType::EMERGENCY;
						break;
					case 'voicemail':
						$types[] = PhoneNumberType::VOICEMAIL;
						break;
					default:
						break;
				}
			}
		}

		if (empty($countries)) {
			// Check for the existence of a country field.
			if (isset($data[$attribute.'_country']) && $this->phone_country($data[$attribute.'_country'])) {
				$countries = array($data[$attribute.'_country']);
			}
			else {
				// No phone country found; validation cannot proceed.
				return FALSE;
			}
		}

		// Now try each country during validation.
		foreach ($countries as $country) {
			$phoneUtil = PhoneNumberUtil::getInstance();
			try {
				$phoneProto = $phoneUtil->parse($value, $country);
				if ($phoneUtil->isValidNumberForRegion($phoneProto, $country)
					&& (empty($types) || in_array($phoneUtil->getNumberType($phoneProto), $types))) {
					return TRUE;
				}
			}
			catch (NumberParseException $e) {}
		}

		return FALSE;
	}

	/**
	 * Provides some arbitrary validation regarding the _country field to only allow
	 * country codes libphonenumber can handle.
	 * If using a package based on umpirsky/country-list, invalidate the option 'ZZ => Unknown or invalid region'.
	 */
	public function phone_country($country)
	{
		return (strlen($country) === 2 && ctype_alpha($country) && ctype_upper($country) && $country != 'ZZ');
	}

}
