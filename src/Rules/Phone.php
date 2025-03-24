<?php

namespace Propaganistas\LaravelPhone\Rules;

use Closure;
use Illuminate\Contracts\Validation\DataAwareRule;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Support\Arr;
use libphonenumber\PhoneNumberType;
use LogicException;
use Propaganistas\LaravelPhone\PhoneNumber;
use Throwable;

class Phone implements DataAwareRule, ValidationRule
{
    /**
     * @var array<string, mixed>
     */
    protected array $data;

    protected ?string $countryField = null;

    /**
     * @var array<string>
     */
    protected array $countries = [];

    /**
     * @var array<PhoneNumberType|string>
     */
    protected array $allowedTypes = [];

    /**
     * @var array<PhoneNumberType|string>
     */
    protected array $blockedTypes = [];

    protected bool $international = false;

    protected bool $lenient = false;

    public function setData(array $data)
    {
        $this->data = $data;

        return $this;
    }

    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (! $this->passes($attribute, $value)) {
            $fail('validation.phone')->translate();
        }
    }

    protected function passes(string $attribute, mixed $value)
    {
        if (! empty($this->allowedTypes) && ! empty($this->blockedTypes)) {
            throw new LogicException('Cannot use "type" and "notType" simultaneously');
        }

        $countries = array_filter([
            $this->getCountryFieldValue($attribute),
            ...$this->countries,
        ]);

        try {
            $phone = (new PhoneNumber($value, $countries))->lenient($this->lenient);

            // Is the country within the allowed list (if applicable)?
            if (! $this->international && ! empty($countries) && ! $phone->isOfCountry($countries)) {
                return false;
            }

            // Is the type within the allowed list (if applicable)?
            if (! empty($this->allowedTypes) && ! $phone->isOfType($this->allowedTypes)) {
                return false;
            }

            // Is the type within the blocked list (if applicable)?
            if (! empty($this->blockedTypes) && $phone->isOfType($this->blockedTypes)) {
                return false;
            }

            return $phone->isValid();
        } catch (Throwable) {
            return false;
        }
    }

    /**
     * @param  array<string>|string  $country
     */
    public function country(array|string $country): self
    {
        $countries = is_array($country) ? $country : func_get_args();

        $this->countries = array_merge($this->countries, $countries);

        return $this;
    }

    public function countryField(string $name): self
    {
        $this->countryField = $name;

        return $this;
    }

    /**
     * @param  PhoneNumberType|string|array<string|PhoneNumberType>  $type
     */
    public function type(PhoneNumberType|string|array $type): self
    {
        $types = is_array($type) ? $type : func_get_args();

        $this->allowedTypes = array_merge($this->allowedTypes, $types);

        return $this;
    }

    /**
     * @param  PhoneNumberType|string|array<string|PhoneNumberType>  $type
     */
    public function notType(PhoneNumberType|string|array $type): self
    {
        $types = is_array($type) ? $type : func_get_args();

        $this->blockedTypes = array_merge($this->blockedTypes, $types);

        return $this;
    }

    public function mobile(): self
    {
        $this->type(PhoneNumberType::MOBILE);

        return $this;
    }

    public function fixed_line(): self
    {
        $this->type(PhoneNumberType::FIXED_LINE);

        return $this;
    }

    public function lenient(): self
    {
        $this->lenient = true;

        return $this;
    }

    public function international(): self
    {
        $this->international = true;

        return $this;
    }

    protected function getCountryFieldValue(string $attribute): ?string
    {
        // Using Arr::get() enables support for nested data.
        return Arr::get($this->data, $this->countryField ?: $attribute.'_country');
    }

    protected function isDataKey($attribute): bool
    {
        // Using Arr::has() enables support for nested data.
        return Arr::has($this->data, $attribute);
    }

    public function setParameters($parameters)
    {
        $parameters = is_array($parameters) ? $parameters : func_get_args();

        foreach ($parameters as $parameter) {
            if (str_starts_with($parameter, '!') && $this->isTypeName($notParameter = substr($parameter, 1))) {
                $this->notType($notParameter);
            } elseif (strcasecmp('lenient', $parameter) === 0) {
                $this->lenient();
            } elseif (strcasecmp('international', $parameter) === 0) {
                $this->international();
            } elseif ($this->isTypeName($parameter)) {
                $this->type($parameter);
            } elseif ($this->isDataKey($parameter)) {
                $this->countryField = $parameter;
            } elseif (PhoneNumber::isValidCountry($parameter)) {
                $this->country($parameter);
            }
        }

        return $this;
    }

    protected function isTypeName(string $name): bool
    {
        try {
            PhoneNumber::normalizeType($name);

            return true;
        } catch (Throwable) {
        }

        return false;
    }
}
