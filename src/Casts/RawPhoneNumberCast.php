<?php

namespace Propaganistas\LaravelPhone\Casts;

use InvalidArgumentException;
use Propaganistas\LaravelPhone\PhoneNumber;

class RawPhoneNumberCast extends PhoneNumberCast
{
    /**
     * @param  string|null  $value
     */
    public function get($model, string $key, $value, array $attributes): ?PhoneNumber
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
     * @param  string|null|PhoneNumber  $value
     */
    public function set($model, string $key, $value, array $attributes): string
    {
        if ($value instanceof PhoneNumber) {
            return $value->getRawNumber();
        }

        return (string) $value;
    }

    /**
     * @param  PhoneNumber|null  $value
     */
    public function serialize($model, string $key, $value, array $attributes): ?string
    {
        if (! $value) {
            return null;
        }

        return $value->getRawNumber();
    }
}
