<?php namespace Propaganistas\LaravelPhone;

use Illuminate\Validation\Validator;

class PhoneValidator extends Validator
{
	/**
	 * Creates a new instance of PhoneValidator
	 */
	public function __construct($translator, $data, $rules, $messages)
	{
		parent::__construct($translator, $data, $rules, $messages);
	}

	/**
	 * Validates a phone number field using libphonenumber.
	 */
	public function validatePhone($attribute, $value, $parameters)
	{
		// Check if there's a country field present?
		$country = $this->data[$attribute.'_country'];
		if (!$this->validatePhoneCountry($attribute.'_country', $country, array())) {
			return FALSE;
		}

		$phoneUtil = \libphonenumber\PhoneNumberUtil::getInstance();
		$phoneProto = $phoneUtil->parse($value, $country);
		return $phoneUtil->isValidNumber($phoneProto);
	}

	/**
	 * Provides some arbitrary validation regarding the _country field to only allow
	 * country codes libphonenumber can handle.
	 * If using a package based on umpirsky/country-list, invalidate the option 'ZZ => Unknown or invalid region'.
	 */
	public function validatePhoneCountry($attribute, $value, $parameters)
	{
		return (strlen($value) === 2 && ctype_alpha($value) && ctype_upper($value) && $value != 'ZZ');
	}

}