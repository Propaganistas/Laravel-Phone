<?php

namespace Propaganistas\LaravelPhone\Models;

use Illuminate\Contracts\Database\Eloquent\CastsAttributes;
use Propaganistas\LaravelPhone\Exceptions\InvalidCastException;
use Propaganistas\LaravelPhone\PhoneNumber;

class PhoneNumberCast implements CastsAttributes
{
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
        return PhoneNumber::make($value, $this->resolvePhoneCountry($model, $key, $attributes));
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
        $phoneNumber = $value instanceof PhoneNumber
            ? $value
            : $this->get($model, $key, $value, $attributes);

        return [
            $key => $phoneNumber->getRawNumber(),
            $model->getPhoneNumberCountryColumn($key) => $phoneNumber->getCountry(),
        ];
    }

    protected function resolvePhoneCountry($model, $key, $attributes)
    {
        if (!$model instanceof HasPhoneNumber) {
            throw InvalidCastException::invalid($model, $key);
        }

        return $attributes[$model->getPhoneNumberCountryColumn($key)];
    }
}
