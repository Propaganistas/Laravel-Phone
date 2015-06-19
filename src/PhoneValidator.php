<?php namespace Propaganistas\LaravelPhone;

use libphonenumber\PhoneNumberUtil;
use libphonenumber\NumberParseException;
use libphonenumber\PhoneNumberType;
use Propaganistas\LaravelPhone\Exceptions\NoValidCountryFoundException;
use Propaganistas\LaravelPhone\Exceptions\InvalidParameterException;

class PhoneValidator {

	/**
	 * The phone number attribute.
	 * @var string
	 */
	protected $attribute;

	/**
	 * Data from the validator instance.
	 * @var array
	 */
	protected $data;

	/**
	 * Countries to validate against.
	 * @var array
	 */
	protected $allowedCountries = array();

	/**
	 * Untransformed phone number types to validate against.
	 * @var array
	 */
	protected $untransformedTypes = array();

	/**
	 * Transformed phone number types to validate against.
	 * @var array
	 */
	protected $allowedTypes = array();

	/**
	 * Supplied validator parameters.
	 * @var array
	 */
	protected $parameters;

	/**
	 * Validates a phone number.
	 */
	public function validatePhone($attribute, $value, $parameters, $validator)
	{
		$this->attribute = $attribute;
		$this->data = $validator->getData();
		$this->parameters = array_map('strtoupper', $parameters);

		$this->determineCountries();
		$this->determineTypes();
		$this->checkLeftoverParameters();

		// Perform validation.
		foreach ($this->allowedCountries as $country) {
			$phoneUtil = PhoneNumberUtil::getInstance();
			try {
				$phoneProto = $phoneUtil->parse($value, $country);
				if ($phoneUtil->isValidNumberForRegion($phoneProto, $country)
						&& (empty($this->allowedTypes) || in_array($phoneUtil->getNumberType($phoneProto), $this->allowedTypes))
				) {
					return true;
				}
			} catch (NumberParseException $e) {
				// Proceed to default validation error.
			}
		}

		return false;
	}

	/**
	 * Checks if the supplied string is a valid country code using some arbitrary country validation.
	 * If using a package based on umpirsky/country-list, invalidate the option 'ZZ => Unknown or invalid region'.
	 *
	 * @param string $country
	 * @return bool
	 */
	public function isPhoneCountry($country)
	{
		return (strlen($country) === 2 && ctype_alpha($country) && ctype_upper($country) && $country != 'ZZ');
	}

	/**
	 * Checks if the supplied string is a valid phone number type.
	 *
	 * @param string $type
	 * @return bool
	 */
	public function isPhoneType($type)
	{
		// Legacy support.
		$type = ($type == 'LANDLINE' ? 'FIXED_LINE' : $type);

		return defined($this->constructPhoneTypeConstant($type));
	}

	/**
	 * Constructs the corresponding namespaced class constant for a phone number type.
	 *
	 * @param string $type
	 * @return string
	 */
	protected function constructPhoneTypeConstant($type)
	{
		return '\libphonenumber\PhoneNumberType::' . $type;
	}

	/**
	 * Sets the countries to validate against.
	 *
	 * @throws \Propaganistas\LaravelPhone\Exceptions\NoValidCountryFoundException
	 */
	protected function determineCountries()
	{
		// Check for the existence of a country field.
		$field = $this->attribute . '_country';
		if (isset($this->data[$field])) {
			$this->allowedCountries = ($this->isPhoneCountry($this->data[$field])) ? array($this->data[$field]) : array();
			// No exception should be thrown since empty country fields should validate to false.
		}
		// Else use the supplied parameters.
		else {
			$this->allowedCountries = array_filter($this->parameters, function($item) {
				return $this->isPhoneCountry($item);
			});

			if (empty($this->allowedCountries)) {
				throw new NoValidCountryFoundException;
			}
		}
	}

	/**
	 * Sets the phone number types to validate against.
	 */
	protected function determineTypes()
	{
		// Get phone types.
		$this->untransformedTypes = array_filter($this->parameters, function($item) {
			return $this->isPhoneType($item);
		});

		// Transform valid types to their namespaced class constant.
		$this->allowedTypes = array_map(function($item) {
			return constant($this->constructPhoneTypeConstant($item));
		}, $this->untransformedTypes);

		// Add in the unsure number type if applicable.
		if (array_intersect(['FIXED_LINE', 'MOBILE'], $this->parameters)) {
			$this->allowedTypes[] = PhoneNumberType::FIXED_LINE_OR_MOBILE;
		}
	}

	/**
	 * Checks for parameter leftovers to force developers to write proper code.
	 *
	 * @throws \Propaganistas\LaravelPhone\Exceptions\InvalidParameterException
	 */
	protected function checkLeftoverParameters()
	{
		$leftovers = array_diff($this->parameters, $this->allowedCountries, $this->untransformedTypes);
		if (!empty($leftovers)) {
			throw new InvalidParameterException(implode(', ', $leftovers));
		}
	}

}
