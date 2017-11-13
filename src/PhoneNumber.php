<?php namespace Propaganistas\LaravelPhone;

use Exception;
use Illuminate\Contracts\Support\Jsonable;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use JsonSerializable;
use libphonenumber\PhoneNumberFormat;
use libphonenumber\PhoneNumberUtil;
use Propaganistas\LaravelPhone\Exceptions\NumberFormatException;
use Propaganistas\LaravelPhone\Exceptions\CountryCodeException;
use Propaganistas\LaravelPhone\Exceptions\NumberParseException;
use Propaganistas\LaravelPhone\Traits\ParsesCountries;
use Propaganistas\LaravelPhone\Traits\ParsesFormats;
use Propaganistas\LaravelPhone\Traits\ParsesTypes;
use Serializable;

class PhoneNumber implements Jsonable, JsonSerializable, Serializable
{
    use ParsesCountries,
        ParsesFormats,
        ParsesTypes;

    /**
     * The provided phone number.
     *
     * @var string
     */
    protected $number;

    /**
     * The provided phone country.
     *
     * @var array
     */
    protected $countries = [];

    /**
     * The detected phone country.
     *
     * @var string
     */
    protected $country;

    /**
     * Whether to allow lenient checks (i.e. landline numbers without area codes).
     *
     * @var bool
     */
    protected $lenient = false;

    /**
     * @var \libphonenumber\PhoneNumberUtil
     */
    protected $lib;

    /**
     * Phone constructor.
     *
     * @param string $number
     */
    public function __construct($number)
    {
        $this->number = $number;
        $this->lib = PhoneNumberUtil::getInstance();
    }

    /**
     * Create a phone instance.
     *
     * @param string       $number
     * @param string|array $country
     * @return static
     */
    public static function make($number, $country = [])
    {
        $instance = new static($number);

        return $instance->ofCountry($country);
    }

    /**
     * Set the country to which the phone number belongs to.
     *
     * @param string|array $country
     * @return static
     */
    public function ofCountry($country)
    {
        $countries = is_array($country) ? $country : func_get_args();

        $instance = clone $this;
        $instance->countries = array_unique(
            array_merge($instance->countries, static::parseCountries($countries))
        );

        return $instance;
    }

    /**
     * Format the phone number in international format.
     *
     * @return string
     */
    public function formatInternational()
    {
        return $this->format(PhoneNumberFormat::INTERNATIONAL);
    }

    /**
     * Format the phone number in national format.
     *
     * @return string
     */
    public function formatNational()
    {
        return $this->format(PhoneNumberFormat::NATIONAL);
    }

    /**
     * Format the phone number in E164 format.
     *
     * @return string
     */
    public function formatE164()
    {
        return $this->format(PhoneNumberFormat::E164);
    }

    /**
     * Format the phone number in RFC3966 format.
     *
     * @return string
     */
    public function formatRFC3966()
    {
        return $this->format(PhoneNumberFormat::RFC3966);
    }

    /**
     * Format the phone number in a given format.
     *
     * @param string $format
     * @return string
     * @throws \Propaganistas\LaravelPhone\Exceptions\NumberFormatException
     */
    public function format($format)
    {
        $parsedFormat = static::parseFormat($format);

        if (is_null($parsedFormat)) {
            throw NumberFormatException::invalid($format);
        }

        return $this->lib->format(
            $this->getPhoneNumberInstance(),
            $parsedFormat
        );
    }

    /**
     * Format the phone number in a way that it can be dialled from the provided country.
     *
     * @param string $country
     * @return string
     * @throws \Propaganistas\LaravelPhone\Exceptions\CountryCodeException
     */
    public function formatForCountry($country)
    {
        if (! static::isValidCountryCode($country)) {
            throw CountryCodeException::invalid($country);
        }

        return $this->lib->formatOutOfCountryCallingNumber(
            $this->getPhoneNumberInstance(),
            $country
        );
    }

    /**
     * Format the phone number in a way that it can be dialled from the provided country using a cellphone.
     *
     * @param string $country
     * @param bool   $removeFormatting
     * @return string
     * @throws \Propaganistas\LaravelPhone\Exceptions\CountryCodeException
     */
    public function formatForMobileDialingInCountry($country, $removeFormatting = false)
    {
        if (! static::isValidCountryCode($country)) {
            throw CountryCodeException::invalid($country);
        }

        return $this->lib->formatNumberForMobileDialing(
            $this->getPhoneNumberInstance(),
            $country,
            $removeFormatting
        );
    }

    /**
     * Get the phone number's country.
     *
     * @return string
     */
    public function getCountry()
    {
        if (! $this->country) {
            $this->country = $this->filterValidCountry($this->countries);
        }

        return $this->country;
    }

    /**
     * Check if the phone number is of (a) given country(ies).
     *
     * @param string|array $country
     * @return bool
     */
    public function isOfCountry($country)
    {
        $countries = static::parseCountries($country);

        return in_array($this->getCountry(), $countries);
    }

    /**
     * Filter the provided countries to the one that is valid for the number.
     *
     * @param string|array $countries
     * @return string
     * @throws \Propaganistas\LaravelPhone\Exceptions\NumberParseException
     */
    protected function filterValidCountry($countries)
    {
        $result = Collection::make($countries)
                            ->filter(function ($country) {
                                $instance = $this->lib->parse($this->number, $country);

                                return $this->lenient
                                    ? $this->lib->isPossibleNumber($instance, $country)
                                    : $this->lib->isValidNumberForRegion($instance, $country);
                            })->first();

        // If we got a new result, return it.
        if ($result) {
            return $result;
        }

        // Last resort: try to detect it from an international number.
        if ($this->numberLooksInternational()) {
            $countries[] = null;
        }

        foreach ($countries as $country) {
            $instance = $this->lib->parse($this->number, $country);

            if ($this->lib->isValidNumber($instance)) {
                return $this->lib->getRegionCodeForNumber($instance);
            }
        }

        throw NumberParseException::countryRequired($this->number);
    }

    /**
     * Get the phone number's type.
     *
     * @param bool $asConstant
     * @return string|int|null
     */
    public function getType($asConstant = false)
    {
        $type = $this->lib->getNumberType($this->getPhoneNumberInstance());

        if ($asConstant) {
            return $type;
        }

        $stringType = Arr::get(static::parseTypesAsStrings($type), 0);

        return $stringType ? strtolower($stringType) : null;
    }

    /**
     * Check if the phone number is of (a) given type(s).
     *
     * @param string $type
     * @return bool
     */
    public function isOfType($type)
    {
        $types = static::parseTypes($type);

        return in_array($this->getType(true), $types, true);
    }

    /**
     * Get the PhoneNumber instance of the current number.
     *
     * @return \libphonenumber\PhoneNumber
     */
    public function getPhoneNumberInstance()
    {
        return $this->lib->parse($this->number, $this->getCountry());
    }

    /**
     * Determine whether the phone number seems to be in international format.
     *
     * @return bool
     */
    protected function numberLooksInternational()
    {
        return Str::startsWith($this->number, '+');
    }

    /**
     * Enable lenient number parsing.
     *
     * @return $this
     */
    public function lenient()
    {
        $this->lenient = true;

        return $this;
    }

    /**
     * Convert the phone instance to JSON.
     *
     * @param  int $options
     * @return string
     */
    public function toJson($options = 0)
    {
        return json_encode($this->jsonSerialize(), $options);
    }

    /**
     * Convert the phone instance into something JSON serializable.
     *
     * @return string
     */
    public function jsonSerialize()
    {
        return $this->formatE164();
    }

    /**
     * Convert the phone instance into a string representation.
     *
     * @return string
     */
    public function serialize()
    {
        return $this->formatE164();
    }

    /**
     * Reconstructs the phone instance from a string representation.
     *
     * @param string $serialized
     */
    public function unserialize($serialized)
    {
        $this->lib = PhoneNumberUtil::getInstance();
        $this->number = $serialized;
        $this->country = $this->lib->getRegionCodeForNumber($this->getPhoneNumberInstance());
    }

    /**
     * Convert the phone instance to a formatted number.
     *
     * @return string
     */
    public function __toString()
    {
        // Formatting the phone number could throw an exception, but __toString() doesn't cope well with that.
        // Let's just return the original number in that case.
        try {
            return $this->formatE164();
        } catch (Exception $exception) {
            return $this->number;
        }
    }
}