<?php namespace Propaganistas\LaravelPhone\Tests;

use libphonenumber\PhoneNumberType;
use libphonenumber\PhoneNumberFormat;
use Propaganistas\LaravelPhone\PhoneNumber;
use Propaganistas\LaravelPhone\Exceptions\CountryCodeException;
use Propaganistas\LaravelPhone\Exceptions\NumberParseException;
use Propaganistas\LaravelPhone\Exceptions\NumberFormatException;

class PhoneNumberTest extends TestCase
{
    /** @test */
    public function it_can_construct()
    {
        $object = new PhoneNumber('012345678');
        self::assertInstanceOf(PhoneNumber::class, $object);
        self::assertEquals('012345678', (string) $object);
    }

    /** @test */
    public function it_can_return_the_raw_number()
    {
        $object = new PhoneNumber('012 34 56 78');
        self::assertEquals('012 34 56 78', $object->getRawNumber());
    }

    /** @test */
    public function it_can_return_the_country()
    {
        $object = new PhoneNumber('012345678');
        $object = $object->ofCountry('NL', 'FR', 'BE');
        self::assertEquals('BE', $object->getCountry());

        $object = new PhoneNumber('+3212345678');
        self::assertEquals('BE', $object->getCountry());
    }

    /** @test */
    public function it_will_ignore_invalid_countries()
    {
        $object = new PhoneNumber('012345678');
        $object = $object->ofCountry('BE', 'foo', 23);
        self::assertEquals('BE', $object->getCountry());
    }

    /** @test */
    public function it_stores_the_country()
    {
        $object = new PhoneNumber('012345678');
        $object = $object->ofCountry('NL', 'FR', 'BE');
        self::assertEquals('BE', $object->getCountry());

        $object = new PhoneNumber('+3212345678');
        $object->getCountry();
        self::assertEquals('BE', $object->getCountry());
    }

    /** @test */
    public function it_can_check_the_country()
    {
        $object = new PhoneNumber('012345678');
        $object = $object->ofCountry('BE');
        self::assertTrue($object->isOfCountry('BE'));
        self::assertFalse($object->isOfCountry('US'));

        $object = new PhoneNumber('+3212345678');
        self::assertTrue($object->isOfCountry('BE'));
        self::assertFalse($object->isOfCountry('US'));
    }

    /** @test */
    public function it_can_make()
    {
        $object = PhoneNumber::make('012345678');
        self::assertInstanceOf(PhoneNumber::class, $object);
        self::assertEquals('012345678', (string) $object);

        $object = PhoneNumber::make('012345678', 'BE');
        self::assertEquals('+3212345678', (string) $object);
        self::assertEquals('BE', $object->getCountry());

        $object = PhoneNumber::make('012345678', ['BE', 'NL']);
        self::assertEquals('+3212345678', (string) $object);
        self::assertEquals('BE', $object->getCountry());
    }

    /** @test */
    public function it_can_format()
    {
        $object = new PhoneNumber('012345678');
        $object = $object->ofCountry('BE');
        self::assertEquals('+3212345678', $object->format(PhoneNumberFormat::E164));
    }

    /** @test */
    public function it_can_format_international_numbers_without_given_country()
    {
        $object = new PhoneNumber('+3212345678');
        self::assertEquals('012 34 56 78', $object->format(PhoneNumberFormat::NATIONAL));
    }

    /** @test */
    public function it_can_format_international_numbers_with_wrong_country()
    {
        $object = new PhoneNumber('+3212345678');
        $object = $object->ofCountry('US');
        self::assertEquals('012 34 56 78', $object->format(PhoneNumberFormat::NATIONAL));
    }

    /** @test */
    public function it_throws_an_exception_when_formatting_non_international_number_without_given_country()
    {
        $object = new PhoneNumber('012345678');

        $this->expectException(NumberParseException::class);
        $this->expectExceptionMessage('Number requires a country to be specified');
        $object->format(PhoneNumberFormat::NATIONAL);
    }

    /** @test */
    public function it_can_parse_format_names()
    {
        $object = new PhoneNumber('012345678');
        $object = $object->ofCountry('BE');
        self::assertEquals(
            $object->format(PhoneNumberFormat::E164),
            $object->format('e164')
        );
    }

    /** @test */
    public function it_throws_an_exception_for_invalid_formats()
    {
        $object = new PhoneNumber('012345678');
        $object = $object->ofCountry('BE');

        $this->expectException(NumberFormatException::class);
        $this->expectExceptionMessage('foo');
        $object->format('foo');
    }

    /** @test */
    public function it_has_an_international_format_shortcut_method()
    {
        $object = new PhoneNumber('012345678');
        $object = $object->ofCountry('BE');
        self::assertEquals(
            $object->format(PhoneNumberFormat::INTERNATIONAL),
            $object->formatInternational()
        );
    }

    /** @test */
    public function it_has_a_national_format_shortcut_method()
    {
        $object = new PhoneNumber('012345678');
        $object = $object->ofCountry('BE');
        self::assertEquals(
            $object->format(PhoneNumberFormat::NATIONAL),
            $object->formatNational()
        );
    }

    /** @test */
    public function it_has_an_E164_format_shortcut_method()
    {
        $object = new PhoneNumber('012345678');
        $object = $object->ofCountry('BE');
        self::assertEquals(
            $object->format(PhoneNumberFormat::E164),
            $object->formatE164()
        );
    }

    /** @test */
    public function it_has_an_RFC3966_format_shortcut_method()
    {
        $object = new PhoneNumber('012345678');
        $object = $object->ofCountry('BE');
        self::assertEquals(
            $object->format(PhoneNumberFormat::RFC3966),
            $object->formatRFC3966()
        );
    }

    /** @test */
    public function it_can_format_for_dialing_from_within_a_given_country()
    {
        $object = new PhoneNumber('012345678');
        $object = $object->ofCountry('BE');
        self::assertEquals('012 34 56 78', $object->formatForCountry('BE'));
        self::assertEquals('00 32 12 34 56 78', $object->formatForCountry('NL'));
        self::assertEquals('011 32 12 34 56 78', $object->formatForCountry('US'));
    }

    /** @test */
    public function it_can_format_for_dialing_on_mobile_from_within_a_given_country()
    {
        $object = new PhoneNumber('012345678');
        $object = $object->ofCountry('BE');
        self::assertEquals('012345678', $object->formatForMobileDialingInCountry('BE'));
        self::assertEquals('+3212345678', $object->formatForMobileDialingInCountry('NL'));
        self::assertEquals('+3212345678', $object->formatForMobileDialingInCountry('US'));
    }

    /** @test */
    public function it_throws_an_exception_when_an_invalid_country_is_provided_for_formatting_for_dialing()
    {
        $object = new PhoneNumber('+3212345678');

        $this->expectException(CountryCodeException::class);
        $this->expectExceptionMessage('foo');
        $object->formatForCountry('foo');
    }

    /** @test */
    public function it_throws_an_exception_when_an_invalid_country_is_provided_for_formatting_for_mobile_dialing()
    {
        $object = new PhoneNumber('+3212345678');
        $this->expectException(CountryCodeException::class);
        $this->expectExceptionMessage('foo');
        $object->formatForMobileDialingInCountry('foo');
    }

    /** @test */
    public function it_can_verify_formats()
    {
        self::assertTrue(PhoneNumber::isValidFormat(PhoneNumberFormat::E164));
        self::assertTrue(PhoneNumber::isValidFormat('e164'));
        self::assertFalse(PhoneNumber::isValidFormat(99999));
        self::assertFalse(PhoneNumber::isValidFormat('foo'));
    }

    /** @test */
    public function it_throws_an_exception_when_the_country_is_missing()
    {
        $object = new PhoneNumber('45678');
        $this->expectException(NumberParseException::class);
        $this->expectExceptionMessage('Number requires a country to be specified.');
        $object->formatRFC3966();
    }

    /** @test */
    public function it_throws_an_exception_when_the_country_is_mismatched()
    {
        $object = new PhoneNumber('4567');
        $object = $object->ofCountry('BE');

        $this->expectException(NumberParseException::class);
        $this->expectExceptionMessage('Number does not match the provided country');
        $object->formatRFC3966();
    }

    /** @test */
    public function it_throws_an_exception_when_an_international_number_also_fails_with_provided_country()
    {
        $object = new PhoneNumber('+15555555555');
        $object = $object->ofCountry('US');

        $this->expectException(NumberParseException::class);
        $this->expectExceptionMessage('Number does not match the provided country');
        $object->formatRFC3966();
    }

    /** @test */
    public function it_can_return_the_type()
    {
        $object = new PhoneNumber('012345678');
        $object = $object->ofCountry('BE');
        self::assertEquals('fixed_line', $object->getType());
        self::assertEquals(PhoneNumberType::FIXED_LINE, $object->getType(true));

        $object = new PhoneNumber('0470123456');
        $object = $object->ofCountry('BE');
        self::assertEquals('mobile', $object->getType());
        self::assertEquals(PhoneNumberType::MOBILE, $object->getType(true));
    }

    /** @test */
    public function it_can_check_the_type()
    {
        $object = new PhoneNumber('012345678');
        $object = $object->ofCountry('BE');
        self::assertTrue($object->isOfType('fixed_line'));
        self::assertTrue($object->isOfType(PhoneNumberType::FIXED_LINE));
        self::assertFalse($object->isOfType('mobile'));
        self::assertFalse($object->isOfType(PhoneNumberType::MOBILE));

        $object = new PhoneNumber('0470123456');
        $object = $object->ofCountry('BE');
        self::assertFalse($object->isOfType('fixed_line'));
        self::assertFalse($object->isOfType(PhoneNumberType::FIXED_LINE));
        self::assertTrue($object->isOfType('mobile'));
        self::assertTrue($object->isOfType(PhoneNumberType::MOBILE));
    }

    /* @test */
    public function it_adds_the_unsure_type()
    {
        // This number is of type FIXED_LINE_OR_MOBILE.
        // Without the unsure type, the following check would fail.
        $object = new PhoneNumber('8590332334');
        $object = $object->ofCountry('IN');
        self::assertTrue($object->isOfType('fixed_line'));
    }

    /** @test */
    public function it_can_verify_types()
    {
        self::assertTrue(PhoneNumber::isValidType(PhoneNumberType::MOBILE));
        self::assertTrue(PhoneNumber::isValidType((string) PhoneNumberType::MOBILE));
        self::assertTrue(PhoneNumber::isValidType('mobile'));
        self::assertFalse(PhoneNumber::isValidType(99999));
        self::assertFalse(PhoneNumber::isValidType('99999'));
        self::assertFalse(PhoneNumber::isValidType('foo'));
    }

    /** @test */
    public function it_can_handle_json_encoding()
    {
        $object = new PhoneNumber('012345678');
        $object = $object->ofCountry('BE');

        self::assertEquals('"+3212345678"', $object->toJson());
        self::assertEquals('"+3212345678"', json_encode($object));
    }

    /** @test */
    public function it_can_handle_serialization()
    {
        $object = new PhoneNumber('012345678');
        $object = $object->ofCountry('BE');
        $serialized = serialize($object);
        self::assertTrue(is_string($serialized));

        $unserialized = unserialize($serialized);
        self::assertInstanceOf(PhoneNumber::class, $unserialized);

        self::assertEquals('+3212345678', (string) $unserialized);
        self::assertEquals('BE', $unserialized->getCountry());
    }

    /** @test */
    public function it_can_be_cast_to_string()
    {
        $object = new PhoneNumber('012345678');
        $object = $object->ofCountry('BE');
        self::assertEquals($object->formatE164(), (string) $object);
    }

    /** @test */
    public function it_returns_the_original_number_when_unparsable_number_is_cast_to_string()
    {
        $object = new PhoneNumber('45678');
        self::assertEquals('45678', (string) $object);

        $object = $object->ofCountry('BE');
        self::assertEquals('45678', (string) $object);
    }

    /** @test */
    public function it_returns_empty_string_when_null_is_cast_to_string()
    {
        $object = new PhoneNumber(null);
        self::assertEquals('', (string) $object);
    }

    /** @test */
    public function it_has_a_helper_function()
    {
        // Test international landline number without country and format parameters.
        $actual = phone('+32 12 34 56 78');
        $expected = PhoneNumber::make('012345678', 'BE');
        self::assertEquals($expected, (string) $actual);

        // Test landline number without format parameter.
        $actual = phone('012345678', 'BE');
        $expected = PhoneNumber::make('012345678', 'BE');
        self::assertEquals($expected, $actual);

        // Test landline number with format parameter.
        $actual = phone('012345678', 'BE', PhoneNumberFormat::NATIONAL);
        $expected = '012 34 56 78';
        self::assertEquals($expected, $actual);
    }

    /** @test */
    public function it_can_get_the_exceptions_number()
    {
        $exception = NumberParseException::countryRequired('12345');
        self::assertEquals('12345', $exception->getNumber());

        $exception = NumberParseException::countryMismatch('12345', []);
        self::assertEquals('12345', $exception->getNumber());
    }

    /** @test */
    public function it_can_get_the_exceptions_countries()
    {
        $exception = NumberParseException::countryMismatch('12345', ['BE', 'foo']);
        self::assertEquals(['BE', 'foo'], $exception->getCountries());
    }

    /** @test */
    public function it_doesnt_throw_for_antarctica()
    {
        $object = new PhoneNumber('012345678');
        $object = $object->ofCountry('AQ','BE');

        self::assertEquals('BE', $object->getCountry());
    }
}
