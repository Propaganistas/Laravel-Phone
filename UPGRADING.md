# Upgrading

- [From 5.x to 6.x](#from-5x-to-6x)
- [From <5.3 to >=5.3](#from-53-to-53)
- [From 4.x to 5.x](#from-4x-to-5x)

## From 5.x to 6.x

`libphonenumber` [shifted](https://github.com/giggsey/libphonenumber-for-php-lite/releases/tag/9.0.0) from using class
constants to native enums. Version 6.0.0 has feature-parity with the last version of 5.x, but the codebase
has been reworked to harness the power of libphonenumber's enums.

**Estimated time to upgrade: 0 to 15 minutes**

### PHP type hints

Type hints have been added to parameters and return types to the codebase where applicable. While already working code
should continue to operate properly, you might be warned by your IDE about mismatching types.

### Validation

#### String-based validation rules

If you made use of constants in *string-based* validation rules, you'll need to update your code.
Treat the reference correctly as an enum or make use of other methods to reference the type.

```php
'phonefield'    => 'phone:'.libphonenumber\PhoneNumberType::MOBILE,
// becomes
'phonefield'    => 'phone:'.libphonenumber\PhoneNumberType::MOBILE->value,
'phonefield'    => 'phone:mobile',
// or use the rule object
```

#### Object-based validation rules

If you referenced a number's type in an object-based validation rule by its explicit integer value (e.g. when stored
somewhere), you'll need to update your code. Cast or convert the value first to an enum or make use of other methods to
validate
on type.

```php
'phonefield'    => (new Phone)->type(1),
// becomes
'phonefield'    => (new Phone)->type(libphonenumber\PhoneNumberType::from(1)),
'phonefield'    => (new Phone)->type(libphonenumber\PhoneNumberType::MOBILE),
// or use a string-based rule
```

#### Rename of shortcut method

If you used the shortcut method to validate on fixed line phone numbers, you'll need to update the method's name.
It has been converted to snake case to match the corresponding enum case more aptly.

```php
(new Phone)->fixedLine();
// becomes
(new Phone)->fixed_line();
```

### Utility PhoneNumber class

#### Bubbling of `libphonenumber` exceptions

The package's custom exceptions (e.g. `NumberParseException`, `CountryCodeException`, `NumberFormatException` and
`IncompatibleTypesException`) have been removed. The package now bubbles `libphonenumber`'s exceptions, which is
normally only narrowed to `libphonenumber\NumberParseException`.

#### Object creation

The signature of `PhoneNumber::__construct()` has changed. It now doesn't accept `null` anymore.

|                    | Before      | After                   |
|--------------------|-------------|-------------------------|
| Parameter $number  | ?string     | string                  |
| Parameter $country | mixed       | array \| string \| null |
| Returns            | PhoneNumber | PhoneNumber             |

#### PhoneNumberType: from constants to enum

The signature of the `getType()` method has changed. It now returns an enum of `libphonenumber\PhoneNumberType` instead
of a string.

|         | Before | After                            |
|---------|--------|----------------------------------|
| Returns | string | `libphonenumber\PhoneNumberType` |

The signature of `isOfType($type)` has changed. If you used a string name or referenced libphonenumber's
`PhoneNumberType` as a constant (e.g. `libphonenumber\PhoneNumberType::MOBILE`),
you're already safe because constants and enums share the same syntax. Yay!
If you're using a PhoneNumberType by its explicit integer value (e.g. when stored somewhere), you'll need to cast or
convert it first.

|                 | Before        | After                                                                          |
|-----------------|---------------|--------------------------------------------------------------------------------|
| Parameter $type | string \| int | string \| `libphonenumber\PhoneNumberType`                                     |
| Returns         | bool          | bool                                                                           |
| Throws          | -             | `InvalidArgumentException` when `$type` is a string and could not be converted |

### PhoneNumberFormat: from constants to enum

The signature of `format($format)` has changed. If you used a string name or referenced libphonenumber's
`PhoneNumberFormat` as a constant (e.g. `libphonenumber\PhoneNumberFormat::NATIONAL`),
you're already safe because constants and enums share the same syntax. Yay!
If you're using a PhoneNumberFormat by its explicit integer value (e.g. when stored somewhere), you'll need to cast or
convert it first.

|                   | Before        | After                                                                            |
|-------------------|---------------|----------------------------------------------------------------------------------|
| Parameter $format | string \| int | string \| `libphonenumber\PhoneNumberFormat`                                     |
| Returns           | string        | string                                                                           |
| Throws            | -             | `InvalidArgumentException` when `$format` is a string and could not be converted |

### Service container

The package previously registered `libphonenumber` as a singleton in the service container. This was never used
internally and as such the registration has been removed completely.
If you relied on the service container to resolve `libphonenumber`, you'll need to add it back in a Service Provider:

```php
$this->app->singleton('libphonenumber', function ($app) {
    return PhoneNumberUtil::getInstance();
});
```

## From <5.3 to >=5.3

The internal dependency `giggsey/libphonenumber-for-php` is now substituted by `giggsey/libphonenumber-for-php-lite`.

`libphonenumber-for-php-lite` is a lightweight drop-in replacement for `libphonenumber-for-php`, significantly reducing
the package size being pulled in. `libphonenumber-for-php-lite` excludes geolocation, carrier information and short
number info.

This is a non-breaking change for functionality provided by `Laravel-Phone`.
However, if you have defined a macro, please review your code and if needed require `giggsey/libphonenumber-for-php` as
an explicit dependency in your project.

## From 4.x to 5.x

The package now minimally requires PHP 8.0 and Laravel 9.0. It also supports Laravel 10.
All documented behavior is preserved. There are just some minor syntactical changes that might need your attention.

**Estimated time to upgrade: 0 to 5 minutes**

### Validation

#### New feature

- The `Phone` rule is now available to be referenced as
  a [rule object](https://laravel.com/docs/9.x/validation#using-rule-objects) (
  `Propaganistas\LaravelPhone\Rules\Phone`):
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

- The `make()` method has been **removed** as it was redundant. Use the `phone()` helper or simply construct a new
  object.
    ```php
    PhoneNumber::make($number, $country)

    // becomes
    phone($number, $country)                 // 1-to-1 replacement ; chainable with subsequent methods
    // or
    new PhoneNumber($number, $country)       // wrap in additional parentheses to chain with subsequent methods
    ```
- the `ofCountry()` method has been **removed**. Specification of possible countries is now only possible while
  constructing the object.
    ```php
    $object = new PhoneNumber($number);
    $object->ofCountry($country);

    // becomes
    $object = new PhoneNumber($number, $country);    // or  phone($number, $country)
    ```
- The **undocumented** public method `numberLooksInternational()` has been removed. There is no alternative.

### Attribute casting

#### Breaking changes

- Similar to the other cast, `RawPhoneNumberCast` will now also throw an exception when it gets invoked with an invalid
  phone object (i.e. while __accessing__ the casted attribute). Make sure to validate phone numbers before persisting
  them and provide the appropriate country code to the cast.
