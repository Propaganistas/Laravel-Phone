<?php

namespace Propaganistas\LaravelPhone\Rules;

use Illuminate\Contracts\Validation\Rule;
use Illuminate\Contracts\Validation\ValidatorAwareRule;
use Illuminate\Support\Arr;
use Illuminate\Validation\Validator;
use libphonenumber\PhoneNumberType as libPhoneNumberType;
use Propaganistas\LaravelPhone\Concerns\PhoneNumberCountry;
use Propaganistas\LaravelPhone\Concerns\PhoneNumberType;
use Propaganistas\LaravelPhone\Exceptions\NumberParseException;
use Propaganistas\LaravelPhone\Exceptions\IncompatibleTypesException;
use Propaganistas\LaravelPhone\PhoneNumber;

class Phone implements Rule, ValidatorAwareRule
{
    protected Validator $validator;

    protected ?string $countryField = null;

    protected array $countries = [];

    protected array $allowedTypes = [];

    protected array $blockedTypes = [];

    protected bool $international = false;

    protected bool $lenient = false;

    public function passes($attribute, $value)
    {
        $countries = PhoneNumberCountry::sanitize([
            $this->getCountryFieldValue($attribute),
            ...$this->countries,
        ]);

        $allowedTypes = PhoneNumberType::sanitize($this->allowedTypes);
        $blockedTypes = PhoneNumberType::sanitize($this->blockedTypes);

        try {
            $phone = (new PhoneNumber($value, $countries))->lenient($this->lenient);

            // Is the country within the allowed list (if applicable)?
            if (! $this->international && ! empty($countries) && ! $phone->isOfCountry($countries)) {
                return false;
            }

            if (! empty($allowedTypes) && ! empty($blockedTypes)) {
                throw IncompatibleTypesException::invalid();
            }

            // Is the type within the allowed list (if applicable)?
            if (! empty($allowedTypes) && ! $phone->isOfType($allowedTypes)) {
                return false;
            }

            // Is the type within the blocked list (if applicable)?
            if (! empty($blockedTypes) && $phone->isOfType($blockedTypes)) {
                return false;
            }

            return $phone->isValid();
        } catch (NumberParseException $e) {
            return false;
        }
    }

    public function country($country)
    {
        $countries = is_array($country) ? $country : func_get_args();

        $this->countries = array_merge($this->countries, $countries);

        return $this;
    }

    public function countryField($name)
    {
        $this->countryField = $name;

        return $this;
    }

    public function type($type)
    {
        $types = is_array($type) ? $type : func_get_args();

        $this->allowedTypes = array_merge($this->allowedTypes, $types);

        return $this;
    }

    public function notType($type)
    {
        $types = is_array($type) ? $type : func_get_args();

        $this->blockedTypes = array_merge($this->blockedTypes, $types);

        return $this;
    }

    public function mobile()
    {
        $this->type(libPhoneNumberType::MOBILE);

        return $this;
    }

    public function fixedLine()
    {
        $this->type(libPhoneNumberType::FIXED_LINE);

        return $this;
    }

    public function lenient()
    {
        $this->lenient = true;

        return $this;
    }

    public function international()
    {
        $this->international = true;

        return $this;
    }

    protected function getCountryFieldValue(string $attribute)
    {
        // Using Arr::get() enables support for nested data.
        return Arr::get($this->validator->getData(), $this->countryField ?: $attribute.'_country');
    }

    protected function isDataKey($attribute): bool
    {
        // Using Arr::has() enables support for nested data.
        return Arr::has($this->validator->getData(), $attribute);
    }

    public function setParameters($parameters)
    {
        $parameters = is_array($parameters) ? $parameters : func_get_args();

        foreach ($parameters as $parameter) {
            if (str_starts_with($parameter, '!')) {
                $parameter = substr($parameter, 1);

                if (ctype_digit($parameter) && PhoneNumberType::isValid((int) $parameter)) {
                    $this->notType((int) $parameter);
                } elseif (PhoneNumberType::isValidName($parameter)) {
                    $this->notType($parameter);
                }
            } elseif (strcasecmp('lenient', $parameter) === 0) {
                $this->lenient();
            } elseif (strcasecmp('international', $parameter) === 0) {
                $this->international();
            } elseif (ctype_digit($parameter) && PhoneNumberType::isValid((int) $parameter)) {
                $this->type((int) $parameter);
            } elseif (PhoneNumberType::isValidName($parameter)) {
                $this->type($parameter);
            } elseif ($this->isDataKey($parameter)) {
                $this->countryField = $parameter;
            } elseif (PhoneNumberCountry::isValid($parameter)) {
                $this->country($parameter);
            }
        }

        return $this;
    }

    public function setValidator($validator)
    {
        $this->validator = $validator;

        return $this;
    }

    public function message()
    {
        return trans('validation.phone');
    }
}
