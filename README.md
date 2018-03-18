# Laravel Phone

[![Build Status](https://travis-ci.org/Propaganistas/Laravel-Phone.svg?branch=master)](https://travis-ci.org/Propaganistas/Laravel-Phone)
[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/Propaganistas/Laravel-Phone/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/Propaganistas/Laravel-Phone/?branch=master)
[![Code Coverage](https://scrutinizer-ci.com/g/Propaganistas/Laravel-Phone/badges/coverage.png?b=master)](https://scrutinizer-ci.com/g/Propaganistas/Laravel-Phone/?branch=master)
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
- [Utility class](#utility-phonenumber-class)
    - [Formatting](#formatting)
    - [Number information](#number-information)
    - [Helper function](#helper-function)

## Demo

Check out the behavior of this package in the [demo](https://laravel-phone.herokuapp.com).

## Installation

Run the following command to install the latest applicable version of the package:

```bash
composer require propaganistas/laravel-phone
```

### Laravel

In your app config, add the Service Provider to the `$providers` array *(only for Laravel 5.4 or below)*:

 ```php
'providers' => [
    ...
    Propaganistas\LaravelPhone\PhoneServiceProvider::class,
],
```

In your languages directory, add for each language an extra language line for the validator:

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

- You provide a dedicated country input field (keyed by *ISO 3166-1 compliant* country codes) to allow end users to supply a country on their own. The easiest method by far is to install the [Laravel-Intl](https://github.com/Propaganistas/Laravel-Intl) package. Make sure the country field is named similar as the phone field but with *_country* appended for automatic discovery, or provide your custom country field name as a parameter to the validator:

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

  If using [Laravel-Intl](https://github.com/Propaganistas/Laravel-Intl), you could then use the following snippet to populate a country selection list. It will automatically present translated country names according to your application locale:

    ```php
    Country::all()
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

## Utility PhoneNumber class

A phone number can be wrapped in the `Propaganistas\LaravelPhone\PhoneNumber` class to enhance it with useful utility methods. It's safe to directly reference these objects in views or when saving to the database as they will degrade gracefully to the E164 format.

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
