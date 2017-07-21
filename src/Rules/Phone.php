<?php namespace Propaganistas\LaravelPhone\Rules;

use libphonenumber\PhoneNumberType;
use Propaganistas\LaravelPhone\Traits\ParsesCountries;
use Propaganistas\LaravelPhone\Traits\ParsesTypes;

class Phone
{
    /**
     * The provided phone countries.
     *
     * @var array
     */
    protected $countries = [];

    /**
     * The input field name to check for a country value.
     *
     * @var string
     */
    protected $countryField;

    /**
     * The provided phone types.
     *
     * @var array
     */
    protected $types = [];

    /**
     * Whether the number's country should be auto-detected.
     *
     * @var bool
     */
    protected $detect = false;

    /**
     * Whether to allow lenient checks (i.e. landline numbers without area codes).
     *
     * @var bool
     */
    protected $lenient = false;

    /**
     * Set the phone countries.
     *
     * @param string|array $country
     * @return $this
     */
    public function country($country)
    {
        $countries = is_array($country) ? $country : func_get_args();

        $this->countries = array_merge($this->countries, $countries);

        return $this;
    }

    /**
     * Set the country input field.
     *
     * @param string $name
     * @return $this
     */
    public function countryField($name)
    {
        $this->countryField = $name;

        return $this;
    }

    /**
     * Set the phone types.
     *
     * @param string|array $type
     * @return $this
     */
    public function type($type)
    {
        $types = is_array($type) ? $type : func_get_args();

        $this->types = array_merge($this->types, $types);

        return $this;
    }

    /**
     * Shortcut method for mobile type restriction.
     *
     * @return $this
     */
    public function mobile()
    {
        $this->type(PhoneNumberType::MOBILE);

        return $this;
    }

    /**
     * Shortcut method for fixed line type restriction.
     *
     * @return $this
     */
    public function fixedLine()
    {
        $this->type(PhoneNumberType::FIXED_LINE);

        return $this;
    }

    /**
     * Enable automatic country detection.
     *
     * @return $this
     */
    public function detect()
    {
        $this->detect = true;

        return $this;
    }

    /**
     * Enable lenient number checking.
     *
     * @return $this
     */
    public function lenient()
    {
        $this->lenient = true;

        return $this;
    }

    /**
     * Convert the rule to a validation string.
     *
     * @return string
     */
    public function __toString()
    {
        $parameters = implode(',', array_merge(
            $this->countries,
            $this->types,
            ($this->countryField ? [$this->countryField]: []),
            ($this->detect ? ['AUTO'] : []),
            ($this->lenient ? ['LENIENT'] : [])
        ));

        return 'phone' . (! empty($parameters) ? ":$parameters" : '');
    }
}