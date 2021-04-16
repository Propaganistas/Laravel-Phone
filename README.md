# Laravel Phone

![Tests](https://github.com/Propaganistas/Laravel-Phone/workflows/Tests/badge.svg?branch=master)
[![Latest Stable Version](https://poser.pugx.org/propaganistas/laravel-phone/v/stable)](https://packagist.org/packages/propaganistas/laravel-phone)
[![Total Downloads](https://poser.pugx.org/propaganistas/laravel-phone/downloads)](https://packagist.org/packages/propaganistas/laravel-phone)
[![License](https://poser.pugx.org/propaganistas/laravel-phone/license)](https://packagist.org/packages/propaganistas/laravel-phone)

Adds phone number functionality to Laravel and Lumen based on the [PHP port](https://github.com/giggsey/libphonenumber-for-php) of [Google's libphonenumber API](https://github.com/googlei18n/libphonenumber) by [giggsey](https://github.com/giggsey).

## Table of Contents

- [Demo](#demo)
- [Installation](#installation)
    - [Laravel](#laravel)
    - [Lumen](#lumen)
- [Validation](#validation)
- [Attribute casting](#attribute-casting)
- [Utility class](#utility-phonenumber-class)
    - [Formatting](#formatting)
    - [Number information](#number-information)
    - [Helper function](#helper-function)
- [Database considerations](#database-considerations)

## Demo

Check out the behavior of this package in the [demo](https://laravel-phone.herokuapp.com).

## Installation

Run the following command to install the latest applicable version of the package:

```bash
composer require propaganistas/laravel-phone
```

### Laravel

The Service Provider gets discovered automatically.

In your languages directory, add an extra line for each language file:

```php
'phone' => 'The :attribute field contains an invalid number.',
```

### Lumen

In `bootstrap/app.php`, register the Service Provider

```php
$app->register(Propaganistas\LaravelPhone\PhoneServiceProvider::class);
```

## Validation

To validate a phone number, use the `phone` keyword in your validation rules array or use the `Phone` rule class to define the rule in an expressive way. The phone validator is able to operate in **three** ways.

- You either specify [*ISO 3166-1 alpha-2 compliant*](http://en.wikipedia.org/wiki/ISO_3166-1_alpha-2#Officially_assigned_code_elements) country codes yourself as parameters for the validator, e.g.:

    ```php
    'phonefield'       => 'phone:US,BE',
    // 'phonefield'    => Rule::phone()->country(['US', 'BE'])
    ```

  The validator will check if the number is valid in at least one of provided countries, so feel free to add as many country codes as you like.

- You provide a dedicated country input field (keyed by *ISO 3166-1 compliant* country codes) to allow end users to supply a country on their own. Make sure the country field has the same name as the phone field but with *_country* appended for automatic discovery, or provide your custom country field name as a parameter to the validator:

    ```php
    'phonefield'            => 'phone',
    // 'phonefield'         => Rule::phone()
    'phonefield_country'    => 'required_with:phonefield',
    ```

    ```php
    'phonefield'            => 'phone:custom_country_field',
    // 'phonefield'         => Rule::phone()->countryField('custom_country_field')
    'custom_country_field'  => 'required_with:phonefield',
    ```

- You instruct the validator to detect which country the number belongs to using the `AUTO` keyword (and optionally any fallback countries):

    ```php
    'phonefield'       => 'phone:AUTO,US',
    // 'phonefield'    => Rule::phone()->detect()->country('US')
    ```

  The validator will try to extract the country from the number itself and then check if the number is valid for that country. If the country could not be guessed it will be validated using the fallback countries if provided. Note that country guessing will only work when phone numbers are entered in *international format* (prefixed with a `+` sign, e.g. +32 ....). Leading double zeros will **NOT** be parsed correctly as this isn't an established consistency.

To specify constraints on the number type, just append the allowed types to the end of the parameters, e.g.:

```php
'phonefield'       => 'phone:US,BE,mobile',
// 'phonefield'    => Rule::phone()->country(['US', 'BE'])->type('mobile')
// 'phonefield'    => Rule::phone()->country('US', 'BE')->mobile()
```
The most common types are `mobile` and `fixed_line`, but feel free to use any of the types defined [here](https://github.com/giggsey/libphonenumber-for-php/blob/master/src/PhoneNumberType.php).

You can also enable more lenient validation (for example, fixed lines without area codes) by using the `LENIENT` parameter. This feature inherently doesn't play well with country autodetection and number type validation, so use such combo at own risk.

```php
'phonefield'       => 'phone:LENIENT,US',
// 'phonefield'    => Rule::phone()->lenient()->country('US')
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
        'phone' => RawPhoneNumberCast::class.':BE',
        'another_phone' => E164PhoneNumberCast::class.':BE',
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
    'phone' => RawPhoneNumberCast::class.':country_field',
    'another_phone' => E164PhoneNumberCast::class.':BE',
];
```

In order to not encounter any unexpected issues when using these casts, please always validate any input using the [validation](#validation) rules previously described.

## Utility PhoneNumber class

A phone number can be wrapped in the `Propaganistas\LaravelPhone\PhoneNumber` class to enhance it with useful utility methods. It's safe to directly reference these objects in views or when saving to the database as they will degrade gracefully to the E.164 format.

```php
use Propaganistas\LaravelPhone\PhoneNumber;

(string) PhoneNumber::make('+3212/34.56.78');              // +3212345678
(string) PhoneNumber::make('012 34 56 78', 'BE');          // +3212345678
(string) PhoneNumber::make('012345678')->ofCountry('BE');  // +3212345678
```

### Formatting
A PhoneNumber can be formatted in various ways:

```php
PhoneNumber::make('012 34 56 78', 'BE')->format($format);       // See libphonenumber\PhoneNumberFormat
PhoneNumber::make('012 34 56 78', 'BE')->formatE164();          // +3212345678
PhoneNumber::make('012 34 56 78', 'BE')->formatInternational(); // +32 12 34 56 78
PhoneNumber::make('012 34 56 78', 'BE')->formatRFC3966();       // +32-12-34-56-78
PhoneNumber::make('012/34.56.78', 'BE')->formatNational();      // 012 34 56 78

// Formats so the number can be called straight from the provided country.
PhoneNumber::make('012 34 56 78', 'BE')->formatForCountry('BE'); // 012 34 56 78
PhoneNumber::make('012 34 56 78', 'BE')->formatForCountry('NL'); // 00 32 12 34 56 78
PhoneNumber::make('012 34 56 78', 'BE')->formatForCountry('US'); // 011 32 12 34 56 78

// Formats so the number can be clicked on and called straight from the provided country using a cellphone.
PhoneNumber::make('012 34 56 78', 'BE')->formatForMobileDialingInCountry('BE'); // 012345678
PhoneNumber::make('012 34 56 78', 'BE')->formatForMobileDialingInCountry('NL'); // +3212345678
PhoneNumber::make('012 34 56 78', 'BE')->formatForMobileDialingInCountry('US'); // +3212345678
```

### Number information
Get some information about the phone number:

```php
PhoneNumber::make('012 34 56 78', 'BE')->getType();              // 'fixed_line'
PhoneNumber::make('012 34 56 78', 'BE')->isOfType('fixed_line'); // true
PhoneNumber::make('012 34 56 78', 'BE')->getCountry();           // 'BE'
PhoneNumber::make('012 34 56 78', 'BE')->isOfCountry('BE');      // true
PhoneNumber::make('+32 12 34 56 78')->isOfCountry('BE');         // true
```

### Helper function

The package exposes the `phone()` helper function that returns a `Propaganistas\LaravelPhone\PhoneNumber` instance or the formatted string if `$format` was provided:

```php
phone($number, $country = [], $format = null)
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
          $user->phone_normalized = preg_replace('[^0-9]', '', $user->phone);
          $user->phone_national = preg_replace('[^0-9]', '', phone($user->phone, $user->phone_country)->formatNational());
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
    $query->where('phone_normalized', 'LIKE', preg_replace('[^0-9]', '', $search) . '%')
          ->orWhere('phone_national', 'LIKE', preg_replace('[^0-9]', '', $search) . '%')
          ->orWhere('phone_e164', 'LIKE', preg_replace('[^+0-9]', '', $search) . '%')
  });
  ```
