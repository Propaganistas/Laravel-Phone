<?php

namespace Propaganistas\LaravelPhone\Casts;

use Propaganistas\LaravelPhone\PhoneNumber;
use UnexpectedValueException;

class E164PhoneNumberCast extends PhoneNumberCast
{
    /**
     * Cast the given value.
     *
     * @param  \Illuminate\Database\Eloquent\Model  $model
     * @param  string  $key
     * @param  mixed  $value
     * @param  array  $attributes
     * @return mixed
     */
    public function get($model, string $key, $value, array $attributes)
    {
        if (! $value) {
            return null;
        }

        $phone = new PhoneNumber($value);

        if (! $phone->numberLooksInternational()) {
            throw new UnexpectedValueException(
                'Queried value for '.$key.' is not in international format'
            );
        }

        return $phone;
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
        if (! $value) {
            return null;
        }

        if (! $value instanceof PhoneNumber) {
            $value = (new PhoneNumber($value))->ofCountry(
                $this->getPossibleCountries($key, $attributes)
            );
        }

        return $value->formatE164();
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
        return $value->formatE164();
    }
}