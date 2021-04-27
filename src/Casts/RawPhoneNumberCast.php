<?php

namespace Propaganistas\LaravelPhone\Casts;

use InvalidArgumentException;
use Propaganistas\LaravelPhone\PhoneNumber;

class RawPhoneNumberCast extends PhoneNumberCast
{
    /**
     * Cast the given value.
     *
     * @param  \Illuminate\Database\Eloquent\Model  $model
     * @param  string  $key
     * @param  mixed  $value
     * @param  array  $attributes
     * @return \Propaganistas\LaravelPhone\PhoneNumber|null
     */
    public function get($model, string $key, $value, array $attributes)
    {
        if (! $value) {
            return null;
        }

        $phone = new PhoneNumber($value);
        $countries = $this->getPossibleCountries($key, $attributes);

        if (empty($countries) && ! $phone->numberLooksInternational()) {
            throw new InvalidArgumentException(
                'Missing country specification for '.$key.' attribute cast'
            );
        }

        return $phone->ofCountry($countries);
    }

    /**
     * Prepare the given value for storage.
     *
     * @param  \Illuminate\Database\Eloquent\Model  $model
     * @param  string  $key
     * @param  mixed  $value
     * @param  array  $attributes
     * @return mixed
     */
    public function set($model, string $key, $value, array $attributes)
    {
        if ($value instanceof PhoneNumber) {
            return $value->getRawNumber();
        }

        return (string) $value;
    }

    /**
     * Serialize the attribute when converting the model to an array.
     *
     * @param  \Illuminate\Database\Eloquent\Model  $model
     * @param  string  $key
     * @param  mixed  $value
     * @param  array  $attributes
     * @return mixed
     */
    public function serialize($model, string $key, $value, array $attributes)
    {
        if (! $value) {
            return null;
        }

        /** @var $value PhoneNumber */
        return $value->getRawNumber();
    }
}
