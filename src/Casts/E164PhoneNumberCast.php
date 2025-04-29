<?php

namespace Propaganistas\LaravelPhone\Casts;

use Illuminate\Database\Eloquent\Model;
use Propaganistas\LaravelPhone\PhoneNumber;
use UnexpectedValueException;

class E164PhoneNumberCast extends PhoneNumberCast
{
    /**
     * Transform the attribute from the underlying model values.
     */
    public function get(Model $model, string $key, mixed $value, array $attributes): ?PhoneNumber
    {
        if (! $value) {
            return null;
        }

        $phone = new PhoneNumber($value);

        if ($phone->getCountry() === null) {
            throw new UnexpectedValueException('Queried value for '.$key.' is not in international format');
        }

        return $phone;
    }

    /**
     * Transform the attribute to its underlying model values.
     *
     * @param  PhoneNumber|string|null  $value
     */
    public function set(Model $model, string $key, mixed $value, array $attributes): ?string
    {
        if (! $value) {
            return null;
        }

        if (! $value instanceof PhoneNumber) {
            $value = new PhoneNumber($value,
                $this->getPossibleCountries($key, $attributes)
            );
        }

        return $value->formatE164();
    }

    /**
     * Serialize the attribute when converting the model to an array.
     */
    public function serialize(Model $model, string $key, mixed $value, array $attributes): ?string
    {
        if (! $value) {
            return null;
        }

        return $value->formatE164();
    }
}
