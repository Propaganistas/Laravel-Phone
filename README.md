# Laravel Phone Validator

[![Build Status](https://travis-ci.org/Propaganistas/Laravel-Phone.svg?branch=master)](https://travis-ci.org/Propaganistas/Laravel-Phone)
[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/Propaganistas/Laravel-Phone/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/Propaganistas/Laravel-Phone/?branch=master)
[![Code Coverage](https://scrutinizer-ci.com/g/Propaganistas/Laravel-Phone/badges/coverage.png?b=master)](https://scrutinizer-ci.com/g/Propaganistas/Laravel-Phone/?branch=master)
[![Latest Stable Version](https://poser.pugx.org/propaganistas/laravel-phone/v/stable)](https://packagist.org/packages/propaganistas/laravel-phone)
[![Total Downloads](https://poser.pugx.org/propaganistas/laravel-phone/downloads)](https://packagist.org/packages/propaganistas/laravel-phone)
[![License](https://poser.pugx.org/propaganistas/laravel-phone/license)](https://packagist.org/packages/propaganistas/laravel-phone)

Adds a phone validator to Laravel 4|5 and Lumen based on the [PHP port](https://github.com/giggsey/libphonenumber-for-php) of [Google's libphonenumber API](https://github.com/googlei18n/libphonenumber) by [giggsey](https://github.com/giggsey).

### Installation

Run the following command to install the latest version of the package

```bash
composer require propaganistas/laravel-phone
```

**Laravel**

In your app config, add the Service Provider to the `$providers` array

 ```php
'providers' => [
    ...
    Propaganistas\LaravelPhone\LaravelPhoneServiceProvider::class,
],
```

In your languages directory, add for each language an extra language line for the validator:

```php
"phone" => "The :attribute field contains an invalid number.",
```

**Lumen**

In `bootstrap/app.php`, register the Service Provider

```php
$app->register(Propaganistas\LaravelPhone\LaravelPhoneServiceProvider::class);
```

### Usage

To validate a field using the phone validator, use the `phone` keyword in your validation rules array. The phone validator is able to operate in **three** ways.

- You either specify [*ISO 3166-1 alpha-2 compliant*](http://en.wikipedia.org/wiki/ISO_3166-1_alpha-2#Officially_assigned_code_elements) country codes yourself as parameters for the validator, e.g.:

    ```php
'phonefield'  => 'phone:US,BE',
    ```

  The validator will check if the number is valid in at least one of provided countries, so feel free to add as many country codes as you like.

- You don't specify any parameters but you plug in a dedicated country input field (keyed by *ISO 3166-1 compliant* country codes) to allow end users to supply a country on their own. The easiest method by far is to install the [CountryList package by monarobase](https://github.com/Monarobase/country-list). Make sure the country field is named similar as the phone field but with *_country* appended for automatic discovery, or provide your custom country field name as a parameter to the validator:

    ```php
'phonefield'            => 'phone',
'phonefield_country'    => 'required_with:phonefield',
    ```

    ```php
'phonefield'            => 'phone:custom_country_field',
'custom_country_field'  => 'required_with:phonefield',
    ```

  If using the CountryList package, you could then use the following snippet to populate a country selection list:

    ```php
Countries::getList(App::getLocale(), 'php', 'cldr'))
    ```

- You instruct the validator to detect which country the number belongs to using the `AUTO` keyword (and optionally any fallback countries):

    ```php
'phonefield'  => 'phone:AUTO,US',
    ```

  The validator will try to extract the country from the number itself and then check if the number is valid for that country. If the country could not be guessed it will be validated using the fallback countries if provided. Note that country guessing will only work when phone numbers are entered in *international format* (prefixed with a `+` sign, e.g. +32 ....). Leading double zeros will **NOT** be parsed correctly as this isn't an established consistency.

To specify constraints on the number type, just append the allowed types to the end of the parameters, e.g.:

```php
'phonefield'  => 'phone:US,BE,mobile',
```
The most common types are `mobile` and `fixed_line`, but feel free to use any of the types defined [here](https://github.com/giggsey/libphonenumber-for-php/blob/master/src/libphonenumber/PhoneNumberType.php).

You can also enable more lenient validation (for example, fixed lines without area codes) by using the `LENIENT` parameter. This feature inherently doesn't play well with country autodetection and number type validation, so use such combo at own risk.

```php
'phonefield'  => 'phone:LENIENT,US',
```

### Helper function
Format a fetched phone value using the `phone()` helper function. `$country_code` is the country the phone number belongs to.

```php
phone($phone_number, $country_code = null, $format = PhoneNumberFormat::INTERNATIONAL)
```
If no `$country_code` was given, the current application locale will be used as default.
The `$format` parameter is optional and should be a constant of `libphonenumber\PhoneNumberFormat` (defaults to `libphonenumber\PhoneNumberFormat::INTERNATIONAL`) 
