Laravel Phone Validator
=========

Adds a phone validator to Laravel based on the [PHP port](https://github.com/giggsey/libphonenumber-for-php) of [Google's libphonenumber API](https://code.google.com/p/libphonenumber/) by [giggsey](https://github.com/giggsey).

### Installation

1. To use this package you need to be able generate a country list with ISO 3166-1 codes. The easiest method by far is to install the [CountryList package by monarobase](https://github.com/Monarobase/country-list). Please you can do this before proceeding.

2. In the `require` key of `composer.json` file add the following

        "propaganistas/laravel-phone": "dev-master"

3. Run the Composer update comand

        $ composer update

4. In your `config/app.php` add `'Propaganistas\LaravelPhone\LaravelPhoneServiceProvider',` to the end of the `$providers` array

        'providers' => array(

          'Illuminate\Foundation\Providers\ArtisanServiceProvider',
          'Illuminate\Auth\AuthServiceProvider',
          ...
          'Propaganistas\LaravelPhone\LaravelPhoneServiceProvider',

        ),


### Usage

The validator works analogously as the `confirmed` validation rule, but is named `phone` (and `phone_country`):

    public static $rules = array(
      'call'          => 'phone',
      'call_country'  => 'phone_country',
    );

Each 'phone' field should also have an accompanying `_country` field. Optionally you can choose to append the `phone_country` validator to add some extra validation to ensure proper country codes being passed on.

In your custom view, you could then use:

    {{ Form::label('call', 'Phone number') }}
    {{ Form::text('call') }}

    {{ Form::label('call_country', 'Phone number country') }}
    {{ Form::select('call_country', Countries::getList(App::getLocale(), 'php', 'cldr')) }}