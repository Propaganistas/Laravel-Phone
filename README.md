# Laravel Phone

[![Tests](https://github.com/Propaganistas/Laravel-Phone/actions/workflows/tests.yml/badge.svg?branch=master)](https://github.com/Propaganistas/Laravel-Phone/actions/workflows/tests.yml)
[![Latest Stable Version](https://poser.pugx.org/propaganistas/laravel-phone/v/stable)](https://packagist.org/packages/propaganistas/laravel-phone)
[![Total Downloads](https://poser.pugx.org/propaganistas/laravel-phone/downloads)](https://packagist.org/packages/propaganistas/laravel-phone)
[![License](https://poser.pugx.org/propaganistas/laravel-phone/license)](https://packagist.org/packages/propaganistas/laravel-phone)

Adds phone number functionality to Laravel based on the [PHP port](https://github.com/giggsey/libphonenumber-for-php-lite) of [libphonenumber by Google](https://github.com/googlei18n/libphonenumber).

## Table of Contents

- [Demo](#demo)
- [Installation](#installation)
- [Validation](#validation)
- [Attribute casting](#attribute-casting)
- [Utility class](#utility-phonenumber-class)
    - [Formatting](#formatting)
    - [Number information](#number-information)
    - [Equality comparison](#equality-comparison)
- [Database considerations](#database-considerations)

## Demo

Check out the behavior of this package in the [demo](https://laravel-phone.herokuapp.com).

## Installation

Run the following command to install the latest applicable version of the package:

```bash
composer require propaganistas/laravel-phone
```

The Service Provider gets discovered automatically by Laravel.

In your languages directory, add an extra translation in every `validation.php` language file:

```php
'phone' => 'The :attribute field must be a valid number.',
```

## Validation

Use the `phone` keyword in your validation rules array or use the `Propaganistas\LaravelPhone\Rules\Phone` rule class to define the rule in an expressive way.

To put constraints on the allowed originating countries, you can explicitly specify the allowed country codes.

```php
'phonefield'       => 'phone:US,BE',
// 'phonefield'    => (new Phone)->country(['US', 'BE'])
```

Or to make things more dynamic, you can also match against another data field holding a country code. For example, to require a phone number to match the provided country of residence.
Make sure the country field has the same name as the phone field but with `_country` appended for automatic discovery, or provide your custom country field name as a parameter to the validator:

```php
'phonefield'            => 'phone',
// 'phonefield'         => (new Phone)
'phonefield_country'    => 'required_with:phonefield',
```

```php
'phonefield'            => 'phone:custom_country_field',
// 'phonefield'         => (new Phone)->countryField('custom_country_field')
'custom_country_field'  => 'required_with:phonefield',
```

Note: country codes should be [*ISO 3166-1 alpha-2 compliant*](http://en.wikipedia.org/wiki/ISO_3166-1_alpha-2#Officially_assigned_code_elements).

To support _any valid internationally formatted_ phone number next to the whitelisted countries, use the `INTERNATIONAL` parameter. This can be useful when you're expecting locally formatted numbers from a specific country but also want to accept any other foreign number entered properly:

```php
'phonefield'            => 'phone:INTERNATIONAL,BE',
// 'phonefield'         => (new Phone)->international()->country('BE')
```

To specify constraints on the number type, just append the allowed types to the end of the parameters, e.g.:

```php
'phonefield'       => 'phone:mobile',
// 'phonefield'    => (new Phone)->type('mobile')
```
The most common types are `mobile` and `fixed_line`, but feel free to use any of the types defined [here](https://github.com/giggsey/libphonenumber-for-php/blob/master/src/PhoneNumberType.php).

Prepend a type with an exclamation mark to blacklist it instead. Note that you can never use whitelisted *and* blacklisted types at the same time.

```php
'phonefield'       => 'phone:!mobile',
// 'phonefield'    => (new Phone)->notType('mobile')
```

You can also enable lenient validation by using the `LENIENT` parameter.
With leniency enabled, only the length of the number is checked instead of actual carrier patterns.

```php
'phonefield'       => 'phone:LENIENT',
// 'phonefield'    => (new Phone)->lenient()
```

## Attribute casting

Two cast classes are provided for automatic casting of Eloquent model attributes:

```php
use Illuminate\Database\Eloquent\Model;
use Propaganistas\LaravelPhone\Casts\RawPhoneNumberCast;
use Propaganistas\LaravelPhone\Casts\E164PhoneNumberCast;

class User extends Model
{
    public $casts = [
        'phone_1' => RawPhoneNumberCast::class.':BE',
        'phone_2' => E164PhoneNumberCast::class.':BE',
    ];
}
```

Both classes automatically cast the database value to a PhoneNumber object for further use in your application.

```php
$user->phone // PhoneNumber object or null
```
When setting a value, they both accept a string value or a PhoneNumber object. 
The `RawPhoneNumberCast` mutates the database value to the raw input number, while the `E164PhoneNumberCast` writes a formatted E.164 phone number to the database.

In case of `RawPhoneNumberCast`, the cast needs to be hinted about the phone country in order to properly parse the raw number into a phone object.
In case of `E164PhoneNumberCast` and the value to be set is not already in some international format, the cast needs to be hinted about the phone country in order to properly mutate the value.

Both classes accept cast parameters in the same way:
1. When a similar named attribute exists, but suffixed with `_country` (e.g. phone_country), the cast will detect and use it automatically.
2. Provide another attribute's name as a cast parameter
3. Provide one or several country codes as cast parameters

```php
public $casts = [
    'phone_1' => RawPhoneNumberCast::class.':country_field',
    'phone_2' => E164PhoneNumberCast::class.':BE',
];
```

**Important note:** Both casts expect __valid__ phone numbers in order to smoothly convert from/to PhoneNumber objects. Please validate phone numbers before setting them on a model. Refer to the [validation documentation](#validation) to learn how to validate phone numbers.

#### ⚠️ Attribute assignment and `E164PhoneNumberCast`
Due to the nature of `E164PhoneNumberCast` a valid country attribute is expected if the number is not passed in international format. Since casts are applied in the order of the given values, be sure to set the country attribute _before_ setting the phone number attribute. Otherwise `E164PhoneNumberCast` will encounter an empty country value and throw an unexpected exception.

```php
// Wrong
$model->fill([
    'phone' => '012 34 56 78',
    'phone_country' => 'BE',
]);

// Correct
$model->fill([
    'phone_country' => 'BE',
    'phone' => '012 34 56 78',
]);

// Wrong
$model->phone = '012 34 56 78';
$model->phone_country = 'BE';

// Correct
$model->phone_country = 'BE';
$model->phone = '012 34 56 78';
```

## Utility PhoneNumber class

A phone number can be wrapped in the `Propaganistas\LaravelPhone\PhoneNumber` class to enhance it with useful utility methods. It's safe to directly reference these objects in views or when saving to the database as they will degrade gracefully to the E.164 format.

```php
use Propaganistas\LaravelPhone\PhoneNumber;

(string) new PhoneNumber('+3212/34.56.78');                // +3212345678
(string) new PhoneNumber('012 34 56 78', 'BE');            // +3212345678
```

Alternatively you can use the `phone()` helper function. It returns a `Propaganistas\LaravelPhone\PhoneNumber` instance or the formatted string if `$format` was provided:

```php
phone('+3212/34.56.78');                // PhoneNumber instance
phone('012 34 56 78', 'BE');            // PhoneNumber instance
phone('012 34 56 78', 'BE', $format);   // string
```

### Formatting
A PhoneNumber can be formatted in various ways:

```php
$phone = new PhoneNumber('012/34.56.78', 'BE');

$phone->format($format);       // See libphonenumber\PhoneNumberFormat
$phone->formatE164();          // +3212345678
$phone->formatInternational(); // +32 12 34 56 78
$phone->formatRFC3966();       // tel:+32-12-34-56-78
$phone->formatNational();      // 012 34 56 78

// Formats so the number can be called straight from the provided country.
$phone->formatForCountry('BE'); // 012 34 56 78
$phone->formatForCountry('NL'); // 00 32 12 34 56 78
$phone->formatForCountry('US'); // 011 32 12 34 56 78

// Formats so the number can be clicked on and called straight from the provided country using a cellphone.
$phone->formatForMobileDialingInCountry('BE'); // 012345678
$phone->formatForMobileDialingInCountry('NL'); // +3212345678
$phone->formatForMobileDialingInCountry('US'); // +3212345678
```

### Number information
Get some information about the phone number:

```php
$phone = new PhoneNumber('012 34 56 78', 'BE');

$phone->getType();              // 'fixed_line'
$phone->isOfType('fixed_line'); // true
$phone->getCountry();           // 'BE'
$phone->isOfCountry('BE');      // true
```

### Equality comparison
Check if a given phone number is (not) equal to another one:

```php
$phone = new PhoneNumber('012 34 56 78', 'BE');

$phone->equals('012/34.56.76', 'BE')       // true
$phone->equals('+32 12 34 56 78')          // true
$phone->equals( $anotherPhoneObject )      // true/false

$phone->notEquals('045 67 89 10', 'BE')    // true
$phone->notEquals('+32 45 67 89 10')       // true
$phone->notEquals( $anotherPhoneObject )   // true/false
```

## Database considerations

> Disclaimer: Phone number handling is quite different in each application. The topics mentioned below are therefore meant as a set of thought starters; support will **not** be provided.

Storing phone numbers in a database has always been a speculative topic and there's simply no silver bullet. It all depends on your application's requirements. Here are some things to take into account, along with an implementation suggestion. Your ideal database setup will probably be a combination of some of the pointers detailed below.

### Uniqueness

The E.164 format globally and uniquely identifies a phone number across the world. It also inherently implies a specific country and can be supplied as-is to the `phone()` helper.

You'll need:

* One column to store the phone number
* To format the phone number to E.164 before persisting it

Example:

* User input = `012/45.65.78`
* Database column
  * `phone` (varchar) = `+3212456578`

### Presenting the phone number the way it was inputted

If you store formatted phone numbers the raw user input will unretrievably get lost. It may be beneficial to present your users with their very own inputted phone number, for example in terms of improved user experience. 

You'll need:
* Two columns to store the raw input and the correlated country

Example:

* User input = `012/34.56.78`
* Database columns
  * `phone` (varchar) = `012/34.56.78`
  * `phone_country` (varchar) = `BE`

### Supporting searches

Searching through phone numbers can quickly become ridiculously complex and will always require deep understanding of the context and extent of your application. Here's _a_ possible approach covering quite a lot of "natural" use cases.

You'll need:
* Three additional columns to store searchable variants of the phone number:
  * Normalized input (raw input with all non-alpha characters stripped)
  * National formatted phone number (with all non-alpha characters stripped)
  * E.164 formatted phone number
* Probably a `saving()` observer (or equivalent) to prefill the variants before persistence
* An extensive search query utilizing the searchable variants
  
Example:

* User input = `12/34.56.78`  
* Observer method:
  ```php
  public function saving(User $user)
  {
      if ($user->isDirty('phone') && $user->phone) {
          $user->phone_normalized = preg_replace('/[^0-9]/', '', $user->phone);
          $user->phone_national = preg_replace('/[^0-9]/', '', phone($user->phone, $user->phone_country)->formatNational());
          $user->phone_e164 = phone($user->phone, $user->phone_country)->formatE164();
      }
  }
  ```
* Database columns
  * `phone_normalized` (varchar) = `12345678`
  * `phone_national` (varchar) = `012345678`
  * `phone_e164` (varchar) = `+3212345678`
* Search query:
  ```php
  // $search holds the search term
  User::where(function($query) use ($search) {
    $query->where('phone_normalized', 'LIKE', preg_replace('/[^0-9]/', '', $search) . '%')
          ->orWhere('phone_national', 'LIKE', preg_replace('/[^0-9]/', '', $search) . '%')
          ->orWhere('phone_e164', 'LIKE', preg_replace('/[^+0-9]/', '', $search) . '%')
  });
  ```
