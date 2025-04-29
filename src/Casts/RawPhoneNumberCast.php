<?php

namespace Propaganistas\LaravelPhone\Casts;

use Illuminate\Database\Eloquent\Model;
use InvalidArgumentException;
use Propaganistas\LaravelPhone\PhoneNumber;

class RawPhoneNumberCast extends PhoneNumberCast
{
    /**
     * Transform the attribute from the underlying model values.
     */
    public function get(Model $model, string $key, mixed $value, array $attributes): ?PhoneNumber
    {
        if (! $value) {
            return null;
        }

        $phone = new PhoneNumber($value,
            $this->getPossibleCountries($key, $attributes)
        );

        $country = $phone->getCountry();

        if ($country === null) {
            throw new InvalidArgumentException('Missing country specification for '.$key.' attribute cast');
        }

        return new PhoneNumber($value, $country);
    }

    /**
     * Transform the attribute to its underlying model values.
     *
     * @param  PhoneNumber|string|null  $value
     */
    public function set(Model $model, string $key, mixed $value, array $attributes): ?string
    {
        if ($value instanceof PhoneNumber) {
            return $value->getRawNumber();
        }

        return (string) $value;
    }

    /**
     * Serialize the attribute when converting the model to an array.
     */
    public function serialize(Model $model, string $key, mixed $value, array $attributes): ?string
    {
        if (! $value) {
            return null;
        }

        return $value->getRawNumber();
    }
}
