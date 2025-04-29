<?php

namespace Propaganistas\LaravelPhone;

use Illuminate\Contracts\Support\Jsonable;
use Illuminate\Support\Arr;
use Illuminate\Support\Traits\Macroable;
use InvalidArgumentException;
use JsonSerializable;
use libphonenumber\PhoneNumberFormat;
use libphonenumber\PhoneNumberType;
use libphonenumber\PhoneNumberUtil;
use Throwable;

class PhoneNumber implements Jsonable, JsonSerializable
{
    use Macroable;

    protected string $number;

    protected array $countries;

    protected bool $lenient = false;

    /**
     * @param  array<string>|string|null  $country
     */
    final public function __construct(string $number, array|string|null $country = null)
    {
        $this->number = $number;
        $this->countries = Arr::wrap($country);
    }

    public function getCountry(): ?string
    {
        // Try to detect the country first from the number itself.
        try {
            return PhoneNumberUtil::getInstance()->getRegionCodeForNumber(
                PhoneNumberUtil::getInstance()->parse($this->number)
            );
        } catch (Throwable) {
        }

        // Only then iterate over the provided countries.
        $countries = array_filter($this->countries, function ($country) {
            return is_string($country) && static::isValidCountry($country);
        });

        foreach (array_unique($countries) as $country) {
            try {
                $libPhoneObject = PhoneNumberUtil::getInstance()->parse($this->number, $country);
            } catch (Throwable) {
                continue;
            }

            if ($this->lenient) {
                if (PhoneNumberUtil::getInstance()->isPossibleNumber($libPhoneObject, $country)) {
                    return mb_strtoupper($country);
                }

                continue;
            }

            if (PhoneNumberUtil::getInstance()->isValidNumberForRegion($libPhoneObject, $country)) {
                return PhoneNumberUtil::getInstance()->getRegionCodeForNumber($libPhoneObject);
            }
        }

        return null;
    }

    /**
     * @param  string|array<string>  $country
     */
    public function isOfCountry(array|string $country): bool
    {
        $instance = clone $this;
        $instance->countries = Arr::wrap($country);

        return in_array(
            mb_strtoupper($instance->getCountry()),
            array_map('mb_strtoupper', $instance->countries)
        );
    }

    public static function isValidCountry(string $country): bool
    {
        $supported = PhoneNumberUtil::getInstance()->getSupportedRegions();

        return in_array(
            mb_strtoupper($country),
            array_map('mb_strtoupper', $supported)
        );
    }

    public function getType(): PhoneNumberType
    {
        return PhoneNumberUtil::getInstance()->getNumberType(
            $this->toLibPhoneObject()
        );
    }

    /**
     * @param  PhoneNumberType|string|array<string|PhoneNumberType>  $type
     */
    public function isOfType(PhoneNumberType|string|array $type): bool
    {
        $types = array_map(fn ($value) => static::normalizeType($value), Arr::wrap($type));

        // Add the unsure type when applicable.
        if (in_array(PhoneNumberType::FIXED_LINE, $types) || in_array(PhoneNumberType::MOBILE, $types)) {
            $types[] = PhoneNumberType::FIXED_LINE_OR_MOBILE;
        }

        return in_array($this->getType(), $types, true);
    }

    /** @internal */
    public static function normalizeType(PhoneNumberType|string $type): PhoneNumberType
    {
        if ($type instanceof PhoneNumberType) {
            return $type;
        }

        foreach (PhoneNumberType::cases() as $case) {
            if (mb_strtoupper($case->name) === mb_strtoupper($type)) {
                return $case;
            }
        }

        throw new InvalidArgumentException(sprintf('"%s" could not be matched to a valid PhoneNumberType', $type));
    }

    public function format(PhoneNumberFormat|string $format): string
    {
        return PhoneNumberUtil::getInstance()->format(
            $this->toLibPhoneObject(), static::normalizeFormat($format)
        );
    }

    public function formatInternational(): string
    {
        return $this->format(PhoneNumberFormat::INTERNATIONAL);
    }

    public function formatNational(): string
    {
        return $this->format(PhoneNumberFormat::NATIONAL);
    }

    public function formatE164(): string
    {
        return $this->format(PhoneNumberFormat::E164);
    }

    public function formatRFC3966(): string
    {
        return $this->format(PhoneNumberFormat::RFC3966);
    }

    /** @internal */
    public static function normalizeFormat(PhoneNumberFormat|string $format): PhoneNumberFormat
    {
        if ($format instanceof PhoneNumberFormat) {
            return $format;
        }

        foreach (PhoneNumberFormat::cases() as $case) {
            if (mb_strtoupper($case->name) === mb_strtoupper($format)) {
                return $case;
            }
        }

        throw new InvalidArgumentException(sprintf('"%s" could not be matched to a valid PhoneNumberFormat', $format));
    }

    public function formatForCountry(string $country): string
    {
        if (! static::isValidCountry($country)) {
            throw new InvalidArgumentException(sprintf('"%s" could not be matched to a valid country', $country));
        }

        return PhoneNumberUtil::getInstance()->formatOutOfCountryCallingNumber(
            $this->toLibPhoneObject(),
            $country
        );
    }

    public function formatForMobileDialingInCountry(string $country, bool $withFormatting = false): string
    {
        if (! static::isValidCountry($country)) {
            throw new InvalidArgumentException(sprintf('"%s" could not be matched to a valid country', $country));
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
        } catch (Throwable) {
            return false;
        }
    }

    public function lenient(bool $enable = true): self
    {
        $this->lenient = $enable;

        return $this;
    }

    /**
     * @param  string|array<string>|null  $country
     */
    public function equals(PhoneNumber|string $number, array|string|null $country = null): bool
    {
        try {
            if (! $number instanceof static) {
                $number = new static($number, $country);
            }

            return $this->formatE164() === $number->formatE164();
        } catch (Throwable) {
            return false;
        }
    }

    /**
     * @param  string|array<string>|null  $country
     */
    public function notEquals(PhoneNumber|string $number, array|string|null $country = null): bool
    {
        return ! $this->equals($number, $country);
    }

    public function getRawNumber(): string
    {
        return $this->number;
    }

    /**
     * @throws \libphonenumber\NumberParseException
     */
    public function toLibPhoneObject(): \libphonenumber\PhoneNumber
    {
        return PhoneNumberUtil::getInstance()->parse(
            $this->number, $this->getCountry()
        );
    }

    /**
     * @param  int  $options
     * @return string
     **/
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

    public function __unserialize(array $serialized): void
    {
        $this->number = $serialized['number'];
    }

    public function __toString(): string
    {
        // Formatting the phone number could throw an exception, but __toString() doesn't cope well with that.
        // Let's just return the original number in that case.
        try {
            return $this->formatE164();
        } catch (Throwable) {
            return $this->number;
        }
    }
}
