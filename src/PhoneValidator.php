<?php namespace Propaganistas\LaravelPhone;

use libphonenumber\NumberParseException;
use libphonenumber\PhoneNumberType;
use libphonenumber\PhoneNumberUtil;
use Propaganistas\LaravelPhone\Exceptions\NoValidCountryFoundException;
use Propaganistas\LaravelPhone\Exceptions\InvalidParameterException;

class PhoneValidator
{

    /**
     * @var \libphonenumber\PhoneNumberUtil
     */
    protected $lib;

    /**
     * Whether the country should be auto-detected.
     *
     * @var bool
     */
    protected $autodetect = false;

    /**
     * Whether to allow lenient checking of numbers (i.e. landline without area codes).
     *
     * @var bool
     */
    protected $lenient = false;

    /**
     * Countries to validate against.
     *
     * @var array
     */
    protected $countries = [];

    /**
     * Transformed phone number types to validate against.
     *
     * @var array
     */
    protected $types = [];

    /**
     * PhoneValidator constructor.
     */
    public function __construct(PhoneNumberUtil $lib)
    {
        $this->lib = $lib;
    }

    /**
     * Validates a phone number.
     *
     * @param  string $attribute
     * @param  mixed  $value
     * @param  array  $parameters
     * @param  object $validator
     * @return bool
     * @throws \Propaganistas\LaravelPhone\Exceptions\InvalidParameterException
     * @throws \Propaganistas\LaravelPhone\Exceptions\NoValidCountryFoundException
     */
    public function validatePhone($attribute, $value, array $parameters, $validator)
    {
        $this->assignParameters(array_map('strtoupper', $parameters));

        $this->checkCountries($attribute, $validator);

        // If autodetecting, let's first try without a country.
        // Otherwise use provided countries as default.
        if ($this->autodetect || ($this->lenient && empty($this->countries))) {
            array_unshift($this->countries, null);
        }

        foreach ($this->countries as $country) {
            if ($this->isValidNumber($value, $country)) {
                return true;
            }
        }

        // All specified country validations have failed.
        return false;
    }

    /**
     * Parses the supplied validator parameters.
     *
     * @param array $parameters
     * @throws \Propaganistas\LaravelPhone\Exceptions\InvalidParameterException
     */
    protected function assignParameters(array $parameters)
    {
        $types = array();

        foreach ($parameters as $parameter) {
            if ($this->isPhoneCountry($parameter)) {
                $this->countries[] = $parameter;
            } elseif ($this->isPhoneType($parameter)) {
                $types[] = $parameter;
            } elseif ($parameter == 'AUTO') {
                $this->autodetect = true;
            } elseif ($parameter == 'LENIENT') {
                $this->lenient = true;
            } else {
                // Force developers to write proper code.
                throw new InvalidParameterException($parameter);
            }
        }

        $this->types = $this->parseTypes($types);

    }

    /**
     * Checks the detected countries. Overrides countries if a country field is present.
     * When using a country field, we should validate to false if country is empty so no exception
     * will be thrown.
     *
     * @param string $attribute
     * @param $validator
     * @throws \Propaganistas\LaravelPhone\Exceptions\NoValidCountryFoundException
     */
    protected function checkCountries($attribute, $validator)
    {
        $data = array_dot($validator->getData());
        $countryField = $attribute . '_country';

        if (isset($data[$countryField])) {
            $this->countries = array($data[$countryField]);
        } elseif (!$this->autodetect && !$this->lenient && empty($this->countries)) {
            throw new NoValidCountryFoundException;
        }
    }

    /**
     * Performs the actual validation of the phone number.
     *
     * @param mixed $number
     * @param null  $country
     * @return bool
     */
    protected function isValidNumber($number, $country = null)
    {
        try {
            // Throws NumberParseException if not parsed correctly against the supplied country.
            // If no country was given, tries to discover the country code from the number itself.
            $phoneNumber = $this->lib->parse($number, $country);

            // Check if type is allowed.
            if (empty($this->types) || in_array($this->lib->getNumberType($phoneNumber), $this->types)) {

                // Lenient validation.
                if ($this->lenient) {
                    return $this->lib->isPossibleNumber($phoneNumber, $country);
                }

                // For automatic detection, the number should have a country code.
                if ($phoneNumber->hasCountryCode()) {

                    // Automatic detection:
                    if ($this->autodetect) {
                        // Validate if the international phone number is valid for its contained country.
                        return $this->lib->isValidNumber($phoneNumber);
                    }

                    // Validate number against the specified country.
                    return $this->lib->isValidNumberForRegion($phoneNumber, $country);
                }
            }

        } catch (NumberParseException $e) {
            // Proceed to default validation error.
        }

        return false;
    }

    /**
     * Parses the supplied phone number types.
     *
     * @param array $types
     * @return array
     */
    protected function parseTypes(array $types)
    {
        // Transform types to their namespaced class constant.
        array_walk($types, function(&$type) {
            $type = constant('\libphonenumber\PhoneNumberType::' . $type);
        });

        // Add in the unsure number type if applicable.
        if (array_intersect([PhoneNumberType::FIXED_LINE, PhoneNumberType::MOBILE], $types)) {
            $types[] = PhoneNumberType::FIXED_LINE_OR_MOBILE;
        }

        return $types;
    }

    /**
     * Checks if the supplied string is a valid country code using some arbitrary country validation.
     * If using a package based on umpirsky/country-list, invalidate the option 'ZZ => Unknown or invalid region'.
     *
     * @param  string $country
     * @return bool
     */
    public function isPhoneCountry($country)
    {
        return (strlen($country) === 2 && ctype_alpha($country) && ctype_upper($country) && $country != 'ZZ');
    }

    /**
     * Checks if the supplied string is a valid phone number type.
     *
     * @param  string $type
     * @return bool
     */
    public function isPhoneType($type)
    {
        // Legacy support.
        $type = ($type == 'LANDLINE' ? 'FIXED_LINE' : $type);

        return defined('\libphonenumber\PhoneNumberType::' . $type);
    }

}
