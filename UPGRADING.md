# Upgrading

## From <5.3 to >=5.3

The internal dependency `giggsey/libphonenumber-for-php` is now substituted by `giggsey/libphonenumber-for-php-lite`.

`libphonenumber-for-php-lite` is a lightweight drop-in replacement for `libphonenumber-for-php`, significantly reducing the package size being pulled in. `libphonenumber-for-php-lite` excludes geolocation, carrier information and short number info.

This is a non-breaking change for functionality provided by `Laravel-Phone`.
However, if you have defined a macro, please review your code and if needed require `giggsey/libphonenumber-for-php` as an explicit dependency in your project.

## From 4.x to 5.x

The package now minimally requires PHP 8.0 and Laravel 9.0. It also supports Laravel 10.
All documented behavior is preserved. There are just some minor syntactical changes that might need your attention.

**Estimated time to upgrade: 0 to 5 minutes**

### Validation

#### New feature
- The `Phone` rule is now available to be referenced as a [rule object](https://laravel.com/docs/9.x/validation#using-rule-objects) (`Propaganistas\LaravelPhone\Rules\Phone`):
    ```php
    'phonefield' => (new Phone)->mobile()->country('BE')
    ```

#### Breaking changes
- The `detect()` method of the Rule macro has been **renamed** to `international()` to better describe its behavior.
    ```php
    'phonefield' => Rule::phone()->detect()
    // becomes
    'phonefield' => Rule::phone()->international()
    ```
- The `AUTO` parameter has been **renamed** to `INTERNATIONAL` to better describe its behavior.
    ```php
    'phonefield' => 'phone:AUTO'
    // becomes
    'phonefield' => 'phone:INTERNATIONAL'
    ```

### Utility PhoneNumber class

#### Breaking changes
- The `make()` method has been **removed** as it was redundant. Use the `phone()` helper or simply construct a new object.
    ```php
    PhoneNumber::make($number, $country)

    // becomes
    phone($number, $country)                 // 1-to-1 replacement ; chainable with subsequent methods
    // or
    new PhoneNumber($number, $country)       // wrap in additional parentheses to chain with subsequent methods
    ```
- the `ofCountry()` method has been **removed**. Specification of possible countries is now only possible while constructing the object.
    ```php
    $object = new PhoneNumber($number);
    $object->ofCountry($country);

    // becomes
    $object = new PhoneNumber($number, $country);    // or  phone($number, $country)
    ```
- The **undocumented** public method `numberLooksInternational()` has been removed. There is no alternative.

### Attribute casting

#### Breaking changes
- Similar to the other cast, `RawPhoneNumberCast` will now also throw an exception when it gets invoked with an invalid phone object (i.e. while __accessing__ the casted attribute). Make sure to validate phone numbers before persisting them and provide the appropriate country code to the cast.
