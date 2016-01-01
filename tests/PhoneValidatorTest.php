<?php namespace Propaganistas\LaravelPhone\Tests;

use libphonenumber\PhoneNumberFormat;
use Orchestra\Testbench\TestCase;

class PhoneValidatorTest extends TestCase
{

    protected $validator;

    public function setUp()
    {
        parent::setUp();

        $this->validator = $this->app['validator'];
    }

    private function performValidation($data)
    {
        $rule = 'phone' . (isset($data['rule']) ? ':' . $data['rule'] : '');
        $validator = $this->validator->make(
            array_only($data, ['field', 'field_country']), ['field' => $rule]
        );

        return $validator->passes();
    }

    public function testValidatePhoneWithDefaultCountryNoType()
    {
        // Validator with correct country field.
        $this->assertTrue($this->performValidation(['field' => '016123456', 'rule' => 'BE']));

        // Validator with wrong country value.
        $this->assertFalse($this->performValidation(['field' => '016123456', 'rule' => 'NL']));

        // Validator with multiple country values, one correct.
        $this->assertTrue($this->performValidation(['field' => '016123456', 'rule' => 'BE,NL']));

        // Validator with multiple country values, value correct for second country in list.
        $this->assertTrue($this->performValidation(['field' => '016123456', 'rule' => 'NL,BE']));

        // Validator with multiple wrong country values.
        $this->assertFalse($this->performValidation(['field' => '016123456', 'rule' => 'DE,NL']));
    }

    public function testValidatePhoneWithCountryFieldNoType()
    {
        // Validator with correct country field supplied.
        $this->assertTrue($this->performValidation(['field' => '016123456', 'field_country' => 'BE']));

        // Validator with wrong country field supplied.
        $this->assertFalse($this->performValidation(['field' => '016123456', 'field_country' => 'NL']));
    }

    public function testValidatePhoneWithDefaultCountryWithType()
    {
        // Validator with correct country value, correct type.
        $this->assertTrue($this->performValidation(['field' => '0499123456', 'rule' => 'BE,mobile']));

        // Validator with correct country value, wrong type.
        $this->assertFalse($this->performValidation(['field' => '016123456', 'rule' => 'BE,mobile']));

        // Validator with wrong country value, correct type.
        $this->assertFalse($this->performValidation(['field' => '0499123456', 'rule' => 'NL,mobile']));

        // Validator with wrong country value, wrong type.
        $this->assertFalse($this->performValidation(['field' => '016123456', 'rule' => 'NL,mobile']));

        // Validator with multiple country values, one correct, correct type.
        $this->assertTrue($this->performValidation(['field' => '0499123456', 'rule' => 'BE,NL,mobile']));

        // Validator with multiple country values, one correct, wrong type.
        $this->assertFalse($this->performValidation(['field' => '016123456', 'rule' => 'BE,NL,mobile']));

        // Validator with multiple country values, none correct, correct type.
        $this->assertFalse($this->performValidation(['field' => '0499123456', 'rule' => 'DE,NL,mobile']));

        // Validator with multiple country values, none correct, wrong type.
        $this->assertFalse($this->performValidation(['field' => '016123456', 'rule' => 'DE,NL,mobile']));
    }

    public function testValidatePhoneWithCountryFieldWithType()
    {
        // Validator with correct country field supplied, correct type.
        $this->assertTrue($this->performValidation(['field' => '0499123456', 'rule' => 'mobile', 'field_country' => 'BE']));

        // Validator with correct country field supplied, wrong type.
        $this->assertFalse($this->performValidation(['field' => '016123456', 'rule' => 'mobile', 'field_country' => 'BE'
        ]));

        // Validator with wrong country field supplied, correct type.
        $this->assertFalse($this->performValidation(['field' => '0499123456', 'rule' => 'mobile', 'field_country' => 'NL'
        ]));

        // Validator with wrong country field supplied, wrong type.
        $this->assertFalse($this->performValidation(['field' => '016123456', 'rule' => 'mobile', 'field_country' => 'NL'
        ]));
    }

    public function testValidatePhoneAutomaticDetectionFromInternationalInput()
    {
        // Validator with correct international input.
        $this->assertTrue($this->performValidation(['field' => '+3216123456', 'rule' => 'AUTO']));

        // Validator with wrong international input.
        $this->assertFalse($this->performValidation(['field' => '003216123456', 'rule' => 'AUTO']));

        // Validator with wrong international input.
        $this->assertFalse($this->performValidation(['field' => '+321456', 'rule' => 'AUTO']));

        // Validator with wrong international input but correct default country.
        $this->assertTrue($this->performValidation(['field' => '016123456', 'rule' => 'AUTO,NL,BE']));

        // Validator with wrong international input and wrong default country.
        $this->assertFalse($this->performValidation(['field' => '016123456', 'rule' => 'AUTO,NL,DE']));
    }

    public function testValidatePhoneNoDefaultCountryNoCountryField()
    {
        $this->setExpectedException('Propaganistas\LaravelPhone\Exceptions\NoValidCountryFoundException');

        // Validator with no country field or given country.
        $this->performValidation(['field' => '016123456']);

        // Validator with no country field or given country, wrong type.
        $this->performValidation(['field' => '016123456', 'rule' => 'mobile']);

        // Validator with no country field or given country, correct type.
        $this->performValidation(['field' => '0499123456', 'rule' => 'mobile']);

        // Validator with no country field or given country, correct type, faulty parameter.
        $this->performValidation(['field' => '0499123456', 'rule' => 'mobile,xyz']);
    }

    public function testValidatePhoneLenient()
    {
        // Validator with AU area code, lenient off
        $this->assertFalse($this->performValidation(['field' => '88885555', 'rule' => 'AU']));

        // Validator with AU area code, lenient on
        $this->assertTrue($this->performValidation(['field' => '88885555', 'rule' => 'LENIENT,AU']));

        // Validator with correct country field supplied, lenient on
        $this->assertTrue($this->performValidation(['field' => '88885555', 'rule' => 'LENIENT', 'field_country' => 'AU']));

        // Validator with wrong country field supplied, lenient on
        $this->assertTrue($this->performValidation(['field' => '88885555', 'rule' => 'LENIENT', 'field_country' => 'BE']));

        // Validator with no area code, lenient on
        $this->assertTrue($this->performValidation(['field' => '+16502530000', 'rule' => 'LENIENT']));

        // Validator with US area code, lenient on
        $this->assertTrue($this->performValidation(['field' => '+16502530000', 'rule' => 'LENIENT,US']));

        // Validator with no area code, lenient off
        $this->assertFalse($this->performValidation(['field' => '6502530000', 'rule' => 'LENIENT']));

        // Validator with US area code, lenient on
        $this->assertTrue($this->performValidation(['field' => '6502530000', 'rule' => 'LENIENT,US']));

        // Validator with US area code, lenient off
        $this->assertFalse($this->performValidation(['field' => '2530000', 'rule' => 'LENIENT']));

        // Validator with US area code, lenient on
        $this->assertTrue($this->performValidation(['field' => '2530000', 'rule' => 'LENIENT,US']));
    }

    public function testValidatePhoneFaultyParameters()
    {
        $this->setExpectedException('Propaganistas\LaravelPhone\Exceptions\InvalidParameterException');

        // Validator with given country, correct type, faulty parameter.
        $this->performValidation(['field' => '016123456', 'rule' => 'BE,mobile,xyz']);

        // Validator with country field, correct type, faulty parameter.
        $this->performValidation(['field' => '016123456', 'rule' => 'mobile,xyz', 'field_country' => 'BE']);
    }

    public function testPhoneFormatHelperFunction()
    {
        // Test landline number without format parameter.
        $actual = phone_format('016123456', 'BE');
        $expected = '+32 16 12 34 56';
        $this->assertEquals($expected, $actual);

        // Test landline number with format parameter.
        $actual = phone_format('016123456', 'BE', PhoneNumberFormat::NATIONAL);
        $expected = '016 12 34 56';
        $this->assertEquals($expected, $actual);

        // Test mobile number without format parameter.
        $actual = phone_format('0499123456', 'BE');
        $expected = '+32 499 12 34 56';
        $this->assertEquals($expected, $actual);

        // Test mobile number with format parameter.
        $actual = phone_format('0499123456', 'BE', PhoneNumberFormat::NATIONAL);
        $expected = '0499 12 34 56';
        $this->assertEquals($expected, $actual);
    }

}
