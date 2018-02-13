<?php namespace Propaganistas\LaravelPhone\Validation;

use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use libphonenumber\PhoneNumberUtil;
use Propaganistas\LaravelPhone\Exceptions\InvalidParameterException;
use libphonenumber\NumberParseException;
use Propaganistas\LaravelPhone\PhoneNumber;
use Propaganistas\LaravelPhone\Traits\ParsesCountries;
use Propaganistas\LaravelPhone\Traits\ParsesTypes;

class Phone
{
    use ParsesCountries,
        ParsesTypes;

    /**
     * @var \libphonenumber\PhoneNumberUtil
     */
    protected $lib;

    /**
     * Phone constructor.
     */
    public function __construct()
    {
        $this->lib = PhoneNumberUtil::getInstance();
    }

    /**
     * Validates a phone number.
     *
     * @param  string $attribute
     * @param  mixed  $value
     * @param  array  $parameters
     * @param  object $validator
     * @return bool
     */
    public function validate($attribute, $value, array $parameters, $validator)
    {
        $data = $validator->getData();

        list(
            $countries,
            $types,
            $detect,
            $lenient) = $this->extractParameters($attribute, $parameters, $data);

        // A "null" country is prepended:
        // 1. In case of auto-detection to have the validation run first without supplying a country.
        // 2. In case of lenient validation without provided countries; we still might have some luck...
        if ($detect || ($lenient && empty($countries))) {
            array_unshift($countries, null);
        }

        foreach ($countries as $country) {
            try {
                // Parsing the phone number also validates the country, so no need to do this explicitly.
                // It'll throw a PhoneCountryException upon failure.
                $phoneNumber = PhoneNumber::make($value, $country);

                // Type validation.
                if (! empty($types) && ! $phoneNumber->isOfType($types)) {
                    continue;
                }

                $lenientPhoneNumber = $phoneNumber->lenient()->getPhoneNumberInstance();

                // Lenient validation.
                if ($lenient && $this->lib->isPossibleNumber($lenientPhoneNumber, $country)) {
                    return true;
                }

                $phoneNumberInstance = $phoneNumber->getPhoneNumberInstance();

                // Country detection.
                if ($detect && $this->lib->isValidNumber($phoneNumberInstance)) {
                    return true;
                }

                // Default number+country validation.
                if ($this->lib->isValidNumberForRegion($phoneNumberInstance, $country)) {
                    return true;
                }
            } catch (NumberParseException $e) {
                continue;
            }
        }

        return false;
    }

    /**
     * Parse and extract parameters in the appropriate validation arguments.
     *
     * @param string $attribute
     * @param array  $parameters
     * @param array  $data
     * @return array
     * @throws \Propaganistas\LaravelPhone\Exceptions\InvalidParameterException
     */
    protected function extractParameters($attribute, array $parameters, array $data)
    {
        // Discover if an input field was provided. If not, guess the field's name.
        $inputField = Collection::make($parameters)
                                ->intersect(array_keys(Arr::dot($data)))
                                ->first() ?: "${attribute}_country";

        // Attempt to retrieve the field's value.
        if ($inputCountry = Arr::get($data, $inputField)) {

            if (static::isValidType($inputField)) {
                throw InvalidParameterException::ambiguous($inputField);
            }

            // Invalid country field values should just validate to false, and this is exactly what
            // we're getting when simply excluding invalid values.
            // This will also prevent parameter hijacking through the country field.
            if (static::isValidCountryCode($inputCountry)) {
                $parameters[] = $inputCountry;
            }
        }

        $countries = static::parseCountries($parameters);
        $types = static::parseTypes($parameters);

        // Force developers to write proper code.
        // Since the static parsers return a validated array with preserved keys, we can safely diff against the keys.
        // Unfortunately we can't use $collection->diffKeys() as it's not available yet in earlier 5.* versions.
        $leftovers = array_diff_key($parameters, $types, $countries);
        $leftovers = array_diff($leftovers, ['AUTO', 'LENIENT', $inputField]);

        if (! empty($leftovers)) {
            throw InvalidParameterException::parameters($leftovers);
        }

        return [
            $countries,
            $types,
            in_array('AUTO', $parameters),
            in_array('LENIENT', $parameters),
            $inputField,
        ];
    }
}
