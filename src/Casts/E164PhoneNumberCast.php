<?php

namespace Propaganistas\LaravelPhone\Casts;

use Propaganistas\LaravelPhone\PhoneNumber;
use UnexpectedValueException;

class E164PhoneNumberCast extends PhoneNumberCast
{
    public function get($model, string $key, $value, array $attributes)
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

    public function set($model, string $key, $value, array $attributes)
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

    public function serialize($model, string $key, $value, array $attributes)
    {
        if (! $value) {
            return null;
        }

        return $value->formatE164();
    }
}
