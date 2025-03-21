<?php

namespace Propaganistas\LaravelPhone\Tests;

use libphonenumber\PhoneNumberFormat;
use libphonenumber\PhoneNumberType;
use PHPUnit\Framework\Attributes\Test;
use Propaganistas\LaravelPhone\Exceptions\CountryCodeException;
use Propaganistas\LaravelPhone\Exceptions\NumberFormatException;
use Propaganistas\LaravelPhone\Exceptions\NumberParseException;
use Propaganistas\LaravelPhone\PhoneNumber;

class PhoneNumberTest extends TestCase
{
    #[Test]
    public function it_constructs_without_country()
    {
        $object = new PhoneNumber('012345678');
        $this->assertInstanceOf(PhoneNumber::class, $object);
    }

    #[Test]
    public function it_constructs_with_string_country()
    {
        $object = new PhoneNumber('012345678', 'BE');
        $this->assertInstanceOf(PhoneNumber::class, $object);
    }

    #[Test]
    public function it_constructs_with_array_country()
    {
        $object = new PhoneNumber('012345678', ['BE', 'NL']);
        $this->assertInstanceOf(PhoneNumber::class, $object);
    }

    #[Test]
    public function it_constructs_with_null_country()
    {
        $object = new PhoneNumber('012345678', null);
        $this->assertInstanceOf(PhoneNumber::class, $object);
    }

    #[Test]
    public function it_returns_the_raw_number()
    {
        $object = new PhoneNumber('012 34 56 78');
        $this->assertEquals('012 34 56 78', $object->getRawNumber());
    }

    #[Test]
    public function it_returns_true_when_checking_correct_validity()
    {
        $object = new PhoneNumber('+3212345678');
        $this->assertTrue($object->isValid());

        $object = new PhoneNumber('012345678', 'BE');
        $this->assertTrue($object->isValid());

        $object = new PhoneNumber('012345678', ['NL', 'BE', 'FR']);
        $this->assertTrue($object->isValid());
    }

    #[Test]
    public function it_returns_true_when_checking_correct_validity_with_wrong_country()
    {
        $object = new PhoneNumber('+3212345678', 'US');
        $this->assertTrue($object->isValid());
    }

    #[Test]
    public function it_returns_false_when_checking_incorrect_validity()
    {
        $object = new PhoneNumber('012345678');
        $this->assertFalse($object->isValid());

        $object = new PhoneNumber('012345678', 'NL');
        $this->assertFalse($object->isValid());

        $object = new PhoneNumber('012345678', ['NL', 'FR']);
        $this->assertFalse($object->isValid());

        $object = new PhoneNumber('foo');
        $this->assertFalse($object->isValid());
    }

    #[Test]
    public function it_gets_the_country_for_an_international_number()
    {
        $object = new PhoneNumber('+3212345678');
        $this->assertEquals('BE', $object->getCountry());
    }

    #[Test]
    public function it_gets_the_country_for_a_non_international_number()
    {
        $object = new PhoneNumber('012345678', ['NL', 'BE', 'FR']);
        $this->assertEquals('BE', $object->getCountry());
    }

    #[Test]
    public function it_returns_null_when_country_is_not_found_for_a_non_international_number()
    {
        $object = new PhoneNumber('012345678', ['NL', 'FR']);
        $this->assertNull($object->getCountry());
    }

    #[Test]
    public function it_ignores_invalid_countries()
    {
        $object = new PhoneNumber('012345678', ['BE', 'foo', 23]);
        $this->assertEquals('BE', $object->getCountry());
    }

    #[Test]
    public function it_returns_true_when_checking_correct_country()
    {
        $object = new PhoneNumber('012345678');
        $this->assertTrue($object->isOfCountry('BE'));

        $object = new PhoneNumber('+3212345678');
        $this->assertTrue($object->isOfCountry('BE'));
    }

    #[Test]
    public function it_returns_false_when_checking_incorrect_country_or_null()
    {
        $object = new PhoneNumber('012345678');
        $this->assertFalse($object->isOfCountry('US'));

        $object = new PhoneNumber('+3212345678');
        $this->assertFalse($object->isOfCountry('US'));
    }

    #[Test]
    public function it_ignores_provided_countries_when_checking_country()
    {
        $object = new PhoneNumber('012345678', 'NL');
        $this->assertTrue($object->isOfCountry('BE'));

        $object = new PhoneNumber('012345678', 'BE');
        $this->assertFalse($object->isOfCountry('US'));
    }

    #[Test]
    public function it_checks_libphonenumber_specific_regions_as_country()
    {
        $object = new PhoneNumber('+247501234');
        $this->assertTrue($object->isOfCountry('AC'));
        $this->assertFalse($object->isOfCountry('US'));
    }

    #[Test]
    public function it_doesnt_throw_for_antarctica()
    {
        $object = new PhoneNumber('012345678', ['AQ', 'BE']);
        $this->assertEquals('BE', $object->getCountry());
    }

    #[Test]
    public function it_returns_the_type()
    {
        $object = new PhoneNumber('012345678', 'BE');
        $this->assertEquals('fixed_line', $object->getType());

        $object = new PhoneNumber('0470123456', 'BE');
        $this->assertEquals('mobile', $object->getType());
    }

    #[Test]
    public function it_returns_the_type_value()
    {
        if (enum_exists(PhoneNumberType::class)) {
            $object = new PhoneNumber('012345678', 'BE');
            $this->assertEquals(PhoneNumberType::FIXED_LINE->value, $object->getType(true));

            $object = new PhoneNumber('0470123456', 'BE');
            $this->assertEquals(PhoneNumberType::MOBILE->value, $object->getType(true));
        } else {
            $object = new PhoneNumber('012345678', 'BE');
            $this->assertEquals(PhoneNumberType::FIXED_LINE, $object->getType(true));

            $object = new PhoneNumber('0470123456', 'BE');
            $this->assertEquals(PhoneNumberType::MOBILE, $object->getType(true));
        }
    }

    #[Test]
    public function it_returns_true_when_checking_type_with_correct_name()
    {
        $object = new PhoneNumber('012345678', 'BE');
        $this->assertTrue($object->isOfType('fixed_line'));
        $this->assertFalse($object->isOfType('mobile'));

        $object = new PhoneNumber('0470123456', 'BE');
        $this->assertFalse($object->isOfType('fixed_line'));
        $this->assertTrue($object->isOfType('mobile'));
    }

    #[Test]
    public function it_returns_true_when_checking_type_with_correct_value()
    {
        $object = new PhoneNumber('012345678', 'BE');
        $this->assertTrue($object->isOfType(PhoneNumberType::FIXED_LINE));
        $this->assertFalse($object->isOfType(PhoneNumberType::MOBILE));

        $object = new PhoneNumber('0470123456', 'BE');
        $this->assertFalse($object->isOfType(PhoneNumberType::FIXED_LINE));
        $this->assertTrue($object->isOfType(PhoneNumberType::MOBILE));
    }

    #[Test]
    public function it_returns_false_when_checking_incorrect_type_or_null()
    {
        $object = new PhoneNumber('012345678', 'BE');
        $this->assertFalse($object->isOfType('mobile'));
        $this->assertFalse($object->isOfType(PhoneNumberType::MOBILE));
        $this->assertFalse($object->isOfType('foo'));
        $this->assertFalse($object->isOfType(null));

        $object = new PhoneNumber('0470123456', 'BE');
        $this->assertFalse($object->isOfType('fixed_line'));
        $this->assertFalse($object->isOfType(PhoneNumberType::FIXED_LINE));
        $this->assertFalse($object->isOfType('foo'));
        $this->assertFalse($object->isOfType(null));
    }

    #[Test]
    public function it_adds_the_unsure_type_when_checking_fixed_line_or_mobile()
    {
        // This number is of type FIXED_LINE_OR_MOBILE.
        // Without the unsure type, the following check would fail.
        $object = new PhoneNumber('8590332334', 'IN');
        $this->assertTrue($object->isOfType('fixed_line'));
        $this->assertTrue($object->isOfType('mobile'));
    }

    #[Test]
    public function it_formats_with_format()
    {
        $object = new PhoneNumber('+3212345678');
        $this->assertEquals('012 34 56 78', $object->format(PhoneNumberFormat::NATIONAL));

        if (enum_exists(PhoneNumberFormat::class)) {
            $object = new PhoneNumber('+3212345678');
            $this->assertEquals('012 34 56 78', $object->format(PhoneNumberFormat::NATIONAL->value));
        }
    }

    #[Test]
    public function it_formats_with_format_name()
    {
        $object = new PhoneNumber('+3212345678');
        $this->assertEquals('012 34 56 78', $object->format('national'));
    }

    #[Test]
    public function it_throws_an_exception_when_formatting_invalid_numbers()
    {
        $object = new PhoneNumber('012345678');

        $this->expectException(NumberParseException::class);
        $this->expectExceptionMessage('Number requires a country to be specified');
        $object->format(PhoneNumberFormat::NATIONAL);
    }

    #[Test]
    public function it_throws_an_exception_for_invalid_formats()
    {
        $object = new PhoneNumber('+3212345678');

        $this->expectException(NumberFormatException::class);
        $this->expectExceptionMessage('foo');
        $object->format('foo');
    }

    #[Test]
    public function it_has_an_international_format_shortcut_method()
    {
        $object = new PhoneNumber('+3212345678');

        $this->assertEquals(
            $object->format(PhoneNumberFormat::INTERNATIONAL),
            $object->formatInternational()
        );
    }

    #[Test]
    public function it_has_a_national_format_shortcut_method()
    {
        $object = new PhoneNumber('+3212345678');
        $this->assertEquals(
            $object->format(PhoneNumberFormat::NATIONAL),
            $object->formatNational()
        );
    }

    #[Test]
    public function it_has_an_E164_format_shortcut_method()
    {
        $object = new PhoneNumber('012345678', 'BE');
        $this->assertEquals(
            $object->format(PhoneNumberFormat::E164),
            $object->formatE164()
        );
    }

    #[Test]
    public function it_has_an_RFC3966_format_shortcut_method()
    {
        $object = new PhoneNumber('+3212345678');
        $this->assertEquals(
            $object->format(PhoneNumberFormat::RFC3966),
            $object->formatRFC3966()
        );
    }

    #[Test]
    public function it_accepts_numbers_prefixed_with_something()
    {
        $object = new PhoneNumber('BE+3212345678');
        $this->assertTrue($object->isValid());
        $this->assertEquals('BE', $object->getCountry());
        $this->assertEquals('012 34 56 78', $object->format(PhoneNumberFormat::NATIONAL));

        $object = new PhoneNumber('US+3212345678');
        $this->assertTrue($object->isValid());
        $this->assertEquals('BE', $object->getCountry());
        $this->assertEquals('012 34 56 78', $object->format(PhoneNumberFormat::NATIONAL));
    }

    #[Test]
    public function it_formats_for_dialing_from_within_a_given_country()
    {
        $object = new PhoneNumber('+3212345678');
        $this->assertEquals('012 34 56 78', $object->formatForCountry('BE'));
        $this->assertEquals('00 32 12 34 56 78', $object->formatForCountry('NL'));
        $this->assertEquals('011 32 12 34 56 78', $object->formatForCountry('US'));
    }

    #[Test]
    public function it_formats_for_dialing_on_mobile_from_within_a_given_country()
    {
        $object = new PhoneNumber('012 34 56 78', 'BE');
        $this->assertEquals('012345678', $object->formatForMobileDialingInCountry('BE'));
        $this->assertEquals('+3212345678', $object->formatForMobileDialingInCountry('NL'));
        $this->assertEquals('+3212345678', $object->formatForMobileDialingInCountry('US'));
    }

    #[Test]
    public function it_throws_an_exception_when_an_invalid_country_is_provided_for_formatting_for_dialing()
    {
        $object = new PhoneNumber('+3212345678');

        $this->expectException(CountryCodeException::class);
        $this->expectExceptionMessage('foo');
        $object->formatForCountry('foo');
    }

    #[Test]
    public function it_throws_an_exception_when_an_invalid_country_is_provided_for_formatting_for_mobile_dialing()
    {
        $object = new PhoneNumber('+3212345678');

        $this->expectException(CountryCodeException::class);
        $this->expectExceptionMessage('foo');
        $object->formatForMobileDialingInCountry('foo');
    }

    #[Test]
    public function it_throws_an_exception_on_formatting_when_the_country_is_missing()
    {
        $object = new PhoneNumber('45678');

        $this->expectException(NumberParseException::class);
        $this->expectExceptionMessage('Number requires a country to be specified.');
        $object->formatRFC3966();
    }

    #[Test]
    public function it_throws_an_exception_on_formatting_when_the_country_is_mismatched()
    {
        $object = new PhoneNumber('45678', 'BE');

        $this->expectException(NumberParseException::class);
        $this->expectExceptionMessage('Number does not match the provided country');
        $object->formatRFC3966();
    }

    #[Test]
    public function it_handles_json_encoding()
    {
        $object = new PhoneNumber('+3212345678');

        $this->assertEquals('"+3212345678"', $object->toJson());
        $this->assertEquals('"+3212345678"', json_encode($object));
    }

    #[Test]
    public function it_handles_serialization()
    {
        $object = new PhoneNumber('+3212345678');
        $serialized = serialize($object);
        $this->assertTrue(is_string($serialized));

        $unserialized = unserialize($serialized);
        $this->assertInstanceOf(PhoneNumber::class, $unserialized);

        $this->assertEquals('+3212345678', (string) $unserialized);
        $this->assertEquals('BE', $unserialized->getCountry());
    }

    #[Test]
    public function it_casts_to_string()
    {
        $object = new PhoneNumber('012 34 56 78', 'BE');
        $this->assertEquals($object->formatE164(), (string) $object);
    }

    #[Test]
    public function it_returns_the_original_number_when_unparsable_number_is_cast_to_string()
    {
        $object = new PhoneNumber('45678');
        $this->assertEquals('45678', (string) $object);

        $object = new PhoneNumber('45678', 'BE');
        $this->assertEquals('45678', (string) $object);
    }

    #[Test]
    public function it_returns_empty_string_when_null_is_cast_to_string()
    {
        $object = new PhoneNumber(null);
        $this->assertEquals('', (string) $object);
    }

    #[Test]
    public function it_gets_the_exceptions_number()
    {
        $exception = NumberParseException::countryRequired('12345');
        $this->assertEquals('12345', $exception->getNumber());

        $exception = NumberParseException::countryMismatch('12345', []);
        $this->assertEquals('12345', $exception->getNumber());
    }

    #[Test]
    public function it_gets_the_exceptions_countries()
    {
        $exception = NumberParseException::countryMismatch('12345', ['BE', 'foo']);
        $this->assertEquals(['BE', 'foo'], $exception->getCountries());
    }

    #[Test]
    public function it_can_check_equality()
    {
        $object = new PhoneNumber('012345678', ['AQ', 'BE']);

        $this->assertTrue($object->equals('012345678', 'BE'));
        $this->assertTrue($object->equals('012345678', ['BE', 'NL']));
        $this->assertTrue($object->equals('+3212345678'));
        $this->assertTrue($object->equals(new PhoneNumber('012345678', 'BE')));

        $this->assertFalse($object->equals('012345679', 'BE'));
        $this->assertFalse($object->equals('012345679', ['BE', 'NL']));
        $this->assertFalse($object->equals('+3212345679'));
        $this->assertFalse($object->equals(new PhoneNumber('012345679', 'BE')));
    }

    #[Test]
    public function it_can_check_inequality()
    {
        $object = new PhoneNumber('012345678', ['AQ', 'BE']);

        $this->assertTrue($object->notEquals('012345679', 'BE'));
        $this->assertTrue($object->notEquals('012345679', ['BE', 'NL']));
        $this->assertTrue($object->notEquals('+3212345679'));
        $this->assertTrue($object->notEquals(new PhoneNumber('012345679', 'BE')));

        $this->assertFalse($object->notEquals('012345678', 'BE'));
        $this->assertFalse($object->notEquals('012345678', ['BE', 'NL']));
        $this->assertFalse($object->notEquals('+3212345678'));
        $this->assertFalse($object->notEquals(new PhoneNumber('012345678', 'BE')));
    }

    #[Test]
    public function it_doesnt_throw_for_invalid_numbers_when_checking_equality()
    {
        $object = new PhoneNumber('012345678', ['AQ', 'BE']);

        $this->assertFalse($object->equals('1234'));
        $this->assertFalse($object->equals('012345678', 'NL'));
    }

    #[Test]
    public function it_doesnt_throw_for_invalid_numbers_when_checking_inequality()
    {
        $object = new PhoneNumber('012345678', ['AQ', 'BE']);

        $this->assertTrue($object->notEquals('1234'));
        $this->assertTrue($object->notEquals('012345678', 'NL'));
    }

    #[Test]
    public function helper_function_constructs_without_country()
    {
        $actual = phone('+32 12 34 56 78');
        $expected = new PhoneNumber('+32 12 34 56 78');
        $this->assertEquals($expected, $actual);
    }

    #[Test]
    public function helper_function_constructs_with_string_country()
    {
        $actual = phone('012 34 56 78', 'BE');
        $expected = new PhoneNumber('012 34 56 78', 'BE');
        $this->assertEquals($expected, $actual);
    }

    #[Test]
    public function helper_function_constructs_with_array_country()
    {
        $actual = phone('012 34 56 78', ['BE', 'NL']);
        $expected = new PhoneNumber('012 34 56 78', ['BE', 'NL']);
        $this->assertEquals($expected, $actual);
    }

    #[Test]
    public function helper_function_constructs_with_null_country()
    {
        $actual = phone('+32 12 34 56 78', null);
        $expected = new PhoneNumber('+32 12 34 56 78', null);
        $this->assertEquals($expected, $actual);
    }

    #[Test]
    public function helper_function_formats()
    {
        $actual = phone('012345678', 'BE', PhoneNumberFormat::NATIONAL);
        $expected = '012 34 56 78';
        $this->assertEquals($expected, $actual);
    }
}
