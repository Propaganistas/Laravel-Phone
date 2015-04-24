# Laravel Phone Validator

Adds a phone validator to Laravel 5 based on the [PHP port](https://github.com/giggsey/libphonenumber-for-php) of [Google's libphonenumber API](https://code.google.com/p/libphonenumber/) by [giggsey](https://github.com/giggsey).

### Installation

1. In the `require` key of `composer.json` file add the following

    ```json
    "propaganistas/laravel-phone": "~2.0"
    ```
    
    If you need the Laravel 4 version, you can point at version `~1.2` or head over to the `Laravel4` branch.

2. Run the Composer update comand

    ```bash
    $ composer update
    ```

3. In your `app/config/app.php` add `'Propaganistas\LaravelPhone\LaravelPhoneServiceProvider',` to the end of the `$providers` array

    ```php
    'providers' => [
        'Illuminate\Foundation\Providers\ArtisanServiceProvider',
        'Illuminate\Auth\AuthServiceProvider',
        ...
        'Propaganistas\LaravelPhone\LaravelPhoneServiceProvider',
    ],
    ```

4. In your languages directory, add for each language an extra language line for the validator:

    ```php
    "phone" => "The :attribute field contains an invalid number.",
    ```

### Usage

To validate a field using the phone validator, use the `phone` keyword in your validation rules array. The phone validator is able to operate in two ways.

- You either specify *ISO 3166-1 compliant* country codes yourself as parameters for the validator, e.g.:

    ```php
    public static $rules = [
        'phonefield'  => 'phone:US,BE',
    ];
    ```

  The validator will check if the number is valid in at least one of provided countries, so feel free to add as many country codes as you like.

- Or you don't specify any parameters but you plug in a dedicated country input field (keyed by *ISO 3166-1 compliant* country codes) to allow end users to supply a country on their own. The easiest method by far is to install the [CountryList package by monarobase](https://github.com/Monarobase/country-list). The country field has to be named similar to the phone field but with `_country` appended:

    ```php
    public static $rules = [
        'phonefield'          => 'phone',
        'phonefield_country'  => 'required_with:phonefield',
    ];
    ```

  If using the CountryList package, you could then use the following in the form view:

    ```php
{{ Form::text('phonefield') }}
{{ Form::select('phonefield_country', Countries::getList(App::getLocale(), 'php', 'cldr')) }}
    ```

### Display
Format a fetched phone value using the helper function:

```php
phone_format($phone_number, $country_code, $format = null)
```

The `$format` parameter is optional and should be a constant of `\libphonenumber\PhoneNumberFormat` (defaults to `\libphonenumber\PhoneNumberFormat::INTERNATIONAL`) 
