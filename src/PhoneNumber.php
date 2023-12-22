<?php

namespace Propaganistas\LaravelPhone;

use Exception;
use Illuminate\Contracts\Support\Jsonable;
use Illuminate\Support\Arr;
use Illuminate\Support\Traits\Macroable;
use JsonSerializable;
use libphonenumber\NumberParseException as libNumberParseException;
use libphonenumber\PhoneNumberFormat as libPhoneNumberFormat;
use libphonenumber\PhoneNumberType as libPhoneNumberType;
use libphonenumber\PhoneNumberUtil;
use Propaganistas\LaravelPhone\Concerns\PhoneNumberCountry;
use Propaganistas\LaravelPhone\Concerns\PhoneNumberFormat;
use Propaganistas\LaravelPhone\Concerns\PhoneNumberType;
use Propaganistas\LaravelPhone\Exceptions\CountryCodeException;
use Propaganistas\LaravelPhone\Exceptions\NumberFormatException;
use Propaganistas\LaravelPhone\Exceptions\NumberParseException;

class PhoneNumber implements Jsonable, JsonSerializable
{
    use Macroable;

    protected ?string $number;

    protected array $countries;

    protected bool $lenient = false;

    public function __construct(?string $number, $country = [])
    {
        $this->number = $number;
        $this->countries = Arr::wrap($country);
    }

    public function getCountry(): string|null
    {
        // Try to detect the country first from the number itself.
        try {
            return PhoneNumberUtil::getInstance()->getRegionCodeForNumber(
                PhoneNumberUtil::getInstance()->parse($this->number, 'ZZ')
            );
        } catch (libNumberParseException $e) {
        }

        // Only then iterate over the provided countries.
        $sanitizedCountries = PhoneNumberCountry::sanitize($this->countries);

        foreach ($sanitizedCountries as $country) {
            try {
                $libPhoneObject = PhoneNumberUtil::getInstance()->parse($this->number, $country);
            } catch (libNumberParseException $e) {
                continue;
            }

            if ($this->lenient) {
                if (PhoneNumberUtil::getInstance()->isPossibleNumber($libPhoneObject, $country)) {
                    return strtoupper($country);
                }

                continue;
            }

            if (PhoneNumberUtil::getInstance()->isValidNumberForRegion($libPhoneObject, $country)) {
                return PhoneNumberUtil::getInstance()->getRegionCodeForNumber($libPhoneObject);
            }
        }

        return null;
    }

    public function isOfCountry($country): bool
    {
        $countries = PhoneNumberCountry::sanitize(Arr::wrap($country));

        $instance = clone $this;
        $instance->countries = $countries;

        return in_array($instance->getCountry(), $countries);
    }

    public function getType($asValue = false): int|string
    {
        $type = PhoneNumberUtil::getInstance()->getNumberType($this->toLibPhoneObject());

        return $asValue ? $type : PhoneNumberType::getHumanReadableName($type);
    }

    public function isOfType($type): bool
    {
        $types = PhoneNumberType::sanitize(Arr::wrap($type));

        // Add the unsure type when applicable.
        if (array_intersect([libPhoneNumberType::FIXED_LINE, libPhoneNumberType::MOBILE], $types)) {
            $types[] = libPhoneNumberType::FIXED_LINE_OR_MOBILE;
        }

        return in_array($this->getType(true), $types, true);
    }

    public function format(string|int $format): string
    {
        $sanitizedFormat = PhoneNumberFormat::sanitize($format);

        if (is_null($sanitizedFormat)) {
            throw NumberFormatException::invalid($format);
        }

        return PhoneNumberUtil::getInstance()->format(
            $this->toLibPhoneObject(),
            $sanitizedFormat
        );
    }

    public function formatInternational(): string
    {
        return $this->format(libPhoneNumberFormat::INTERNATIONAL);
    }

    public function formatNational(): string
    {
        return $this->format(libPhoneNumberFormat::NATIONAL);
    }

    public function formatE164(): string
    {
        return $this->format(libPhoneNumberFormat::E164);
    }

    public function formatRFC3966(): string
    {
        return $this->format(libPhoneNumberFormat::RFC3966);
    }

    public function formatForCountry($country): string
    {
        if (! PhoneNumberCountry::isValid($country)) {
            throw CountryCodeException::invalid($country);
        }

        return PhoneNumberUtil::getInstance()->formatOutOfCountryCallingNumber(
            $this->toLibPhoneObject(),
            $country
        );
    }

    public function formatForMobileDialingInCountry($country, $withFormatting = false): string
    {
        if (! PhoneNumberCountry::isValid($country)) {
            throw CountryCodeException::invalid($country);
        }

        return PhoneNumberUtil::getInstance()->formatNumberForMobileDialing(
            $this->toLibPhoneObject(),
            $country,
            $withFormatting
        );
    }

    public function isValid(): bool
    {
        try {
            if ($this->lenient) {
                return PhoneNumberUtil::getInstance()->isPossibleNumber(
                    $this->toLibPhoneObject()
                );
            }

            return PhoneNumberUtil::getInstance()->isValidNumberForRegion(
                $this->toLibPhoneObject(),
                $this->getCountry(),
            );
        } catch (NumberParseException $e) {
            return false;
        }
    }

    public function lenient($enable = true): static
    {
        $this->lenient = $enable;

        return $this;
    }

    public function equals($number, $country = null): bool
    {
        try {
            if (! $number instanceof static) {
                $number = new static($number, $country);
            }

            return $this->formatE164() === $number->formatE164();
        } catch (NumberParseException $e) {
            return false;
        }
    }

    public function notEquals($number, $country = null): bool
    {
        return ! $this->equals($number, $country);
    }

    public function getRawNumber(): string
    {
        return $this->number;
    }

    public function toLibPhoneObject()
    {
        try {
            return PhoneNumberUtil::getInstance()->parse($this->number, $this->getCountry());
        } catch (libNumberParseException $e) {
            empty($this->countries)
                ? throw NumberParseException::countryRequired($this->number)
                : throw NumberParseException::countryMismatch($this->number, $this->countries);
        }
    }

    public function toJson($options = 0)
    {
        return json_encode($this->jsonSerialize(), $options);
    }

    public function jsonSerialize(): string
    {
        return $this->formatE164();
    }

    public function __serialize()
    {
        return ['number' => $this->formatE164()];
    }

    public function __unserialize(array $serialized)
    {
        $this->number = $serialized['number'];
    }

    public function __toString()
    {
        // Formatting the phone number could throw an exception, but __toString() doesn't cope well with that.
        // Let's just return the original number in that case.
        try {
            return $this->formatE164();
        } catch (Exception $e) {
            return (string) $this->number;
        }
    }
}
