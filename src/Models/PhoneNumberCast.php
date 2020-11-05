<?php

namespace Propaganistas\LaravelPhone\Models;

use Illuminate\Contracts\Database\Eloquent\CastsAttributes;
use Propaganistas\LaravelPhone\PhoneNumber;
use Propaganistas\LaravelPhone\Traits\ParsesCountries;

class PhoneNumberCast implements CastsAttributes
{
    use ParsesCountries;

    /** @var array */
    protected $countries = [];

    /**
     * @param string|array $country
     */
    public function __construct($country)
    {
        $this->countries = is_array($country)
            ? $country
            : func_get_args();
    }

    /**
     * Cast the given value.
     *
     * @param \Illuminate\Contracts\Database\Eloquent\Model $model
     * @param string $key
     * @param mixed $value
     * @param array $attributes
     *
     * @return Propaganistas\LaravelPhone\PhoneNumber
     */
    public function get($model, string $key, $value, array $attributes)
    {
        return PhoneNumber::make($value, $this->resolvePhoneCountry($attributes));
    }

    /**
     * Prepare the given value for storage.
     *
     * @param \Illuminate\Database\Eloquent\Model $model
     * @param string $key
     * @param mixed $value
     * @param array $attributes
     *
     * @return array
     */
    public function set($model, string $key, $value, array $attributes)
    {
        return $value instanceof PhoneNumber
            ? $value->getRawNumber()
            : $value;
    }

    protected function resolvePhoneCountry($attributes)
    {
        $parsedCountries = $this->parseCountries($this->countries);

        $countryColumns = array_diff($this->countries, $parsedCountries);

        foreach ($countryColumns as $countryColumn) {
            if ($country = $attributes[$countryColumn]) {
                $parsedCountries[] = $country;
            }
        }

        return $parsedCountries;
    }
}
