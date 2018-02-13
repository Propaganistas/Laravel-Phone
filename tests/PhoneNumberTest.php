<?php namespace Propaganistas\LaravelPhone\Tests;

use libphonenumber\PhoneNumberFormat;
use libphonenumber\PhoneNumberType;
use Propaganistas\LaravelPhone\PhoneNumber;

class PhoneNumberTest extends TestCase
{
    /** @test */
    public function it_can_construct()
    {
        $object = new PhoneNumber('012345678');
        $this->assertInstanceOf(PhoneNumber::class, $object);
        $this->assertAttributeEquals('012345678', 'number', $object);
    }

    /** @test */
    public function it_can_set_temporary_countries()
    {
        $object = new PhoneNumber('012345678');
        $object = $object->ofCountry('BE');
        $this->assertAttributeEquals(['BE'], 'countries', $object);

        $object = $object->ofCountry(['NL', 'FR']);
        $this->assertAttributeEquals(['BE', 'NL', 'FR'], 'countries', $object);

        $object = $object->ofCountry('AU', 'CH');
        $this->assertAttributeEquals(['BE', 'NL', 'FR', 'AU', 'CH'], 'countries', $object);
    }

    /** @test */
    public function it_will_filter_invalid_countries()
    {
        $object = new PhoneNumber('012345678');
        $object = $object->ofCountry('BE', 'foo', 23);
        $this->assertAttributeEquals(['BE'], 'countries', $object);
    }

    /** @test */
    public function it_can_return_the_country()
    {
        $object = new PhoneNumber('012345678');
        $object = $object->ofCountry('NL', 'FR', 'BE');
        $this->assertEquals('BE', $object->getCountry());

        $object = new PhoneNumber('+3212345678');
        $this->assertEquals('BE', $object->getCountry());
    }

    /** @test */
    public function it_stores_the_country()
    {
        $object = new PhoneNumber('012345678');
        $object = $object->ofCountry('NL', 'FR', 'BE');
        $object->getCountry();
        $this->assertAttributeEquals('BE', 'country', $object);

        $object = new PhoneNumber('+3212345678');
        $object->getCountry();
        $this->assertAttributeEquals('BE', 'country', $object);
    }

    /** @test */
    public function it_can_check_the_country()
    {
        $object = new PhoneNumber('012345678');
        $object = $object->ofCountry('BE');
        $this->assertTrue($object->isOfCountry('BE'));
        $this->assertFalse($object->isOfCountry('US'));

        $object = new PhoneNumber('+3212345678');
        $this->assertTrue($object->isOfCountry('BE'));
        $this->assertFalse($object->isOfCountry('US'));
    }

    /** @test */
    public function it_can_make()
    {
        $object = PhoneNumber::make('012345678');
        $this->assertInstanceOf(PhoneNumber::class, $object);
        $this->assertAttributeEquals('012345678', 'number', $object);

        $object = PhoneNumber::make('012345678', 'BE');
        $this->assertAttributeEquals('012345678', 'number', $object);
        $this->assertAttributeEquals(['BE'], 'countries', $object);

        $object = PhoneNumber::make('012345678', ['BE', 'NL']);
        $this->assertAttributeEquals('012345678', 'number', $object);
        $this->assertAttributeEquals(['BE', 'NL'], 'countries', $object);
    }

    /** @test */
    public function it_can_format()
    {
        $object = new PhoneNumber('012345678');
        $object = $object->ofCountry('BE');
        $this->assertEquals('+3212345678', $object->format(PhoneNumberFormat::E164));
    }

    /** @test */
    public function it_can_format_international_numbers_without_given_country()
    {
        $object = new PhoneNumber('+3212345678');
        $this->assertEquals('012 34 56 78', $object->format(PhoneNumberFormat::NATIONAL));
    }

    /**
     * @test
     *
     * @expectedException \Propaganistas\LaravelPhone\Exceptions\NumberParseException
     * @expectedExceptionMessage 012345678
     */
    public function it_throws_an_exception_when_formatting_non_international_number_without_given_country()
    {
        $object = new PhoneNumber('012345678');
        $object->format(PhoneNumberFormat::NATIONAL);
    }

    /** @test */
    public function it_can_parse_format_names()
    {
        $object = new PhoneNumber('012345678');
        $object = $object->ofCountry('BE');
        $this->assertEquals(
            $object->format(PhoneNumberFormat::E164),
            $object->format('e164')
        );
    }

    /**
     * @test
     *
     * @expectedException \Propaganistas\LaravelPhone\Exceptions\NumberFormatException
     * @expectedExceptionMessage foo
     */
    public function it_throws_an_exception_for_invalid_formats()
    {
        $object = new PhoneNumber('012345678');
        $object = $object->ofCountry('BE');
        $object->format('foo');
    }

    /** @test */
    public function it_has_an_international_format_shortcut_method()
    {
        $object = new PhoneNumber('012345678');
        $object = $object->ofCountry('BE');
        $this->assertEquals(
            $object->format(PhoneNumberFormat::INTERNATIONAL),
            $object->formatInternational()
        );
    }

    /** @test */
    public function it_has_a_national_format_shortcut_method()
    {
        $object = new PhoneNumber('012345678');
        $object = $object->ofCountry('BE');
        $this->assertEquals(
            $object->format(PhoneNumberFormat::NATIONAL),
            $object->formatNational()
        );
    }

    /** @test */
    public function it_has_an_E164_format_shortcut_method()
    {
        $object = new PhoneNumber('012345678');
        $object = $object->ofCountry('BE');
        $this->assertEquals(
            $object->format(PhoneNumberFormat::E164),
            $object->formatE164()
        );
    }

    /** @test */
    public function it_has_an_RFC3966_format_shortcut_method()
    {
        $object = new PhoneNumber('012345678');
        $object = $object->ofCountry('BE');
        $this->assertEquals(
            $object->format(PhoneNumberFormat::RFC3966),
            $object->formatRFC3966()
        );
    }

    /** @test */
    public function it_can_format_for_dialing_from_within_a_given_country()
    {
        $object = new PhoneNumber('012345678');
        $object = $object->ofCountry('BE');
        $this->assertEquals('012 34 56 78', $object->formatForCountry('BE'));
        $this->assertEquals('00 32 12 34 56 78', $object->formatForCountry('NL'));
        $this->assertEquals('011 32 12 34 56 78', $object->formatForCountry('US'));
    }

    /** @test */
    public function it_can_format_for_dialing_on_mobile_from_within_a_given_country()
    {
        $object = new PhoneNumber('012345678');
        $object = $object->ofCountry('BE');
        $this->assertEquals('012345678', $object->formatForMobileDialingInCountry('BE'));
        $this->assertEquals('+3212345678', $object->formatForMobileDialingInCountry('NL'));
        $this->assertEquals('+3212345678', $object->formatForMobileDialingInCountry('US'));
    }

    /**
     * @test
     *
     * @expectedException \Propaganistas\LaravelPhone\Exceptions\CountryCodeException
     * @expectedExceptionMessage foo
     */
    public function it_throws_an_exception_when_an_invalid_country_is_provided_for_formatting_for_dialing()
    {
        $object = new PhoneNumber('+3212345678');
        $object->formatForCountry('foo');
    }

    /**
     * @test
     *
     * @expectedException \Propaganistas\LaravelPhone\Exceptions\CountryCodeException
     * @expectedExceptionMessage foo
     */
    public function it_throws_an_exception_when_an_invalid_country_is_provided_for_formatting_for_mobile_dialing()
    {
        $object = new PhoneNumber('+3212345678');
        $object->formatForMobileDialingInCountry('foo');
    }

    /** @test */
    public function it_can_verify_formats()
    {
        $this->assertTrue(PhoneNumber::isValidFormat(PhoneNumberFormat::E164));
        $this->assertTrue(PhoneNumber::isValidFormat('e164'));
        $this->assertFalse(PhoneNumber::isValidFormat(99999));
        $this->assertFalse(PhoneNumber::isValidFormat('foo'));
    }

    /**
     * @test
     *
     * @expectedException \Propaganistas\LaravelPhone\Exceptions\NumberParseException
     * @expectedExceptionMessage 45678
     */
    public function it_throws_an_exception_when_the_number_could_not_be_parsed()
    {
        $object = new PhoneNumber('45678');
        $object = $object->ofCountry('BE');
        $object->formatRFC3966();
    }

    /** @test */
    public function it_can_return_the_type()
    {
        $object = new PhoneNumber('012345678');
        $object = $object->ofCountry('BE');
        $this->assertEquals('fixed_line', $object->getType());
        $this->assertEquals(PhoneNumberType::FIXED_LINE, $object->getType(true));

        $object = new PhoneNumber('0470123456');
        $object = $object->ofCountry('BE');
        $this->assertEquals('mobile', $object->getType());
        $this->assertEquals(PhoneNumberType::MOBILE, $object->getType(true));
    }

    /** @test */
    public function it_can_check_the_type()
    {
        $object = new PhoneNumber('012345678');
        $object = $object->ofCountry('BE');
        $this->assertTrue($object->isOfType('fixed_line'));
        $this->assertTrue($object->isOfType(PhoneNumberType::FIXED_LINE));
        $this->assertFalse($object->isOfType('mobile'));
        $this->assertFalse($object->isOfType(PhoneNumberType::MOBILE));

        $object = new PhoneNumber('0470123456');
        $object = $object->ofCountry('BE');
        $this->assertFalse($object->isOfType('fixed_line'));
        $this->assertFalse($object->isOfType(PhoneNumberType::FIXED_LINE));
        $this->assertTrue($object->isOfType('mobile'));
        $this->assertTrue($object->isOfType(PhoneNumberType::MOBILE));
    }

    /** @test */
    public function it_can_verify_types()
    {
        $this->assertTrue(PhoneNumber::isValidType(PhoneNumberType::MOBILE));
        $this->assertTrue(PhoneNumber::isValidType((string) PhoneNumberType::MOBILE));
        $this->assertTrue(PhoneNumber::isValidType('mobile'));
        $this->assertFalse(PhoneNumber::isValidType(99999));
        $this->assertFalse(PhoneNumber::isValidType('99999'));
        $this->assertFalse(PhoneNumber::isValidType('foo'));
    }

    /** @test */
    public function it_can_handle_json_encoding()
    {
        $object = new PhoneNumber('012345678');
        $object = $object->ofCountry('BE');

        $this->assertEquals('"+3212345678"', $object->toJson());
        $this->assertEquals('"+3212345678"', json_encode($object));
    }

    /** @test */
    public function it_can_handle_serialization()
    {
        $object = new PhoneNumber('012345678');
        $object = $object->ofCountry('BE');
        $serialized = serialize($object);
        $this->assertInternalType('string', $serialized);

        $unserialized = unserialize($serialized);
        $this->assertInstanceOf(PhoneNumber::class, $unserialized);
        $this->assertAttributeEquals('+3212345678', 'number', $unserialized);
        $this->assertAttributeEquals('BE', 'country', $unserialized);
    }

    /** @test */
    public function it_can_be_cast_to_string()
    {
        $object = new PhoneNumber('012345678');
        $object = $object->ofCountry('BE');
        $this->assertEquals($object->formatE164(), (string) $object);
    }

    /** @test */
    public function it_returns_the_original_number_when_unparsable_number_is_cast_to_string()
    {
        $object = new PhoneNumber('45678');
        $this->assertEquals('45678', (string) $object);

        $object = $object->ofCountry('BE');
        $this->assertEquals('45678', (string) $object);
    }

    /** @test */
    public function it_has_a_helper_function()
    {
        // Test international landline number without country and format parameters.
        $actual = phone('+32 12 34 56 78');
        $expected = PhoneNumber::make('012345678', 'BE');
        $this->assertEquals($expected, (string) $actual);

        // Test landline number without format parameter.
        $actual = phone('012345678', 'BE');
        $expected = PhoneNumber::make('012345678', 'BE');
        $this->assertEquals($expected, $actual);

        // Test landline number with format parameter.
        $actual = phone('012345678', 'BE', PhoneNumberFormat::NATIONAL);
        $expected = '012 34 56 78';
        $this->assertEquals($expected, $actual);
    }
}
