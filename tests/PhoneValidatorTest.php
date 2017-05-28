<?php namespace Propaganistas\LaravelPhone\Tests;

use Illuminate\Foundation\Application;
use libphonenumber\PhoneNumber;
use libphonenumber\PhoneNumberFormat;
use libphonenumber\PhoneNumberUtil;
use Orchestra\Testbench\TestCase;
use Phone;
use Propaganistas\LaravelPhone\LaravelPhoneServiceProvider;

class PhoneValidatorTest extends TestCase
{

    protected $validator;

    protected function getPackageProviders()
    {
        return [
            'Propaganistas\LaravelPhone\LaravelPhoneServiceProvider',
        ];
    }

    protected function getPackageAliases()
    {
        return [
            'Phone' => 'Propaganistas\LaravelPhone\LaravelPhoneFacade',
        ];
    }

    public function setUp()
    {
        parent::setUp();

        $this->validator = $this->app['validator'];
    }

    public function testFacade()
    {
        $phoneNumber = Phone::parse('650-429-2057', 'US');
        $this->assertTrue($phoneNumber instanceof PhoneNumber);
    }

    public function testValidatePhoneWithDefaultCountryNoType()
    {
        // Validator with correct country field.
        $this->assertTrue($this->validator->make(
            ['field' => '016123456'],
            ['field' => 'phone:BE'])->passes()
        );

        // Validator with wrong country value.
        $this->assertFalse($this->validator->make(
            ['field' => '016123456'],
            ['field' => 'phone:NL'])->passes()
        );

        // Validator with multiple country values, one correct.
        $this->assertTrue($this->validator->make(
            ['field' => '016123456'],
            ['field' => 'phone:BE,NL'])->passes()
        );

        // Validator with multiple country values, value correct for second country in list.
        $this->assertTrue($this->validator->make(
            ['field' => '016123456'],
            ['field' => 'phone:NL,BE'])->passes()
        );

        // Validator with multiple wrong country values.
        $this->assertFalse($this->validator->make(
            ['field' => '016123456'],
            ['field' => 'phone:DE,NL'])->passes()
        );
    }

    public function testValidatePhoneWithCountryFieldNoType()
    {
        // Validator with correct country field supplied.
        $this->assertTrue($this->validator->make(
            ['field' => '016123456', 'field_country' => 'BE'],
            ['field' => 'phone'])->passes()
        );

        // Validator with wrong country field supplied.
        $this->assertFalse($this->validator->make(
            ['field' => '016123456', 'field_country' => 'NL'],
            ['field' => 'phone'])->passes()
        );
    }

    public function testValidatePhoneWithDefaultCountryWithType()
    {
        // Validator with correct country value, correct type.
        $this->assertTrue($this->validator->make(
            ['field' => '0499123456'],
            ['field' => 'phone:BE,mobile'])->passes()
        );

        // Validator with correct country value, wrong type.
        $this->assertFalse($this->validator->make(
            ['field' => '016123456'],
            ['field' => 'phone:BE,mobile'])->passes()
        );

        // Validator with wrong country value, correct type.
        $this->assertFalse($this->validator->make(
            ['field' => '0499123456'],
            ['field' => 'phone:NL,mobile'])->passes()
        );

        // Validator with wrong country value, wrong type.
        $this->assertFalse($this->validator->make(
            ['field' => '016123456'],
            ['field' => 'phone:NL,mobile'])->passes()
        );

        // Validator with multiple country values, one correct, correct type.
        $this->assertTrue($this->validator->make(
            ['field' => '0499123456'],
            ['field' => 'phone:BE,NL,mobile'])->passes()
        );

        // Validator with multiple country values, one correct, wrong type.
        $this->assertFalse($this->validator->make(
            ['field' => '016123456'],
            ['field' => 'phone:BE,NL,mobile'])->passes()
        );

        // Validator with multiple country values, none correct, correct type.
        $this->assertFalse($this->validator->make(
            ['field' => '0499123456'],
            ['field' => 'phone:DE,NL,mobile'])->passes()
        );

        // Validator with multiple country values, none correct, wrong type.
        $this->assertFalse($this->validator->make(
            ['field' => '016123456'],
            ['field' => 'phone:DE,NL,mobile'])->passes()
        );
    }

    public function testValidatePhoneWithCountryFieldWithType()
    {
        // Validator with correct country field supplied, correct type.
        $this->assertTrue($this->validator->make(
            ['field' => '0499123456', 'field_country' => 'BE'],
            ['field' => 'phone:mobile'])->passes()
        );

        // Validator with correct country field supplied, wrong type.
        $this->assertFalse($this->validator->make(
            ['field' => '016123456', 'field_country' => 'BE'],
            ['field' => 'phone:mobile'])->passes()
        );

        // Validator with wrong country field supplied, correct type.
        $this->assertFalse($this->validator->make(
            ['field' => '0499123456', 'field_country' => 'NL'],
            ['field' => 'phone:mobile'])->passes()
        );

        // Validator with wrong country field supplied, wrong type.
        $this->assertFalse($this->validator->make(
            ['field' => '016123456', 'field_country' => 'NL'],
            ['field' => 'phone:mobile'])->passes()
        );
    }

    public function testValidatePhoneWithCustomCountryField()
    {
        // Validator with correct country field supplied, correct type.
        $this->assertTrue($this->validator->make(
            ['field' => '0499123456', 'country_code' => 'BE'],
            ['field' => 'phone:mobile,country_code'])->passes()
        );

        // Validator with correct country field supplied, wrong type.
        $this->assertFalse($this->validator->make(
            ['field' => '016123456', 'country_code' => 'BE'],
            ['field' => 'phone:mobile,country_code'])->passes()
        );

        // Validator with wrong country field supplied, correct type.
        $this->assertFalse($this->validator->make(
            ['field' => '0499123456', 'country_code' => 'NL'],
            ['field' => 'phone:mobile,country_code'])->passes()
        );

        // Validator with wrong country field supplied, wrong type.
        $this->assertFalse($this->validator->make(
            ['field' => '016123456', 'country_code' => 'NL'],
            ['field' => 'phone:mobile,country_code'])->passes()
        );
    }

    public function testValidatePhoneAutomaticDetectionFromInternationalInput()
    {
        // Validator with correct international input.
        $this->assertTrue($this->validator->make(
            ['field' => '+3216123456'],
            ['field' => 'phone:AUTO'])->passes()
        );

        // Validator with wrong international input.
        $this->assertFalse($this->validator->make(
            ['field' => '003216123456'],
            ['field' => 'phone:AUTO'])->passes()
        );

        // Validator with wrong international input.
        $this->assertFalse($this->validator->make(
            ['field' => '+321234'],
            ['field' => 'phone:AUTO'])->passes()
        );

        // Validator with wrong international input but correct default country.
        $this->assertTrue($this->validator->make(
            ['field' => '016123456'],
            ['field' => 'phone:AUTO,NL,BE'])->passes()
        );

        // Validator with wrong international input and wrong default country.
        $this->assertFalse($this->validator->make(
            ['field' => '016123456'],
            ['field' => 'phone:AUTO,DE,NL'])->passes()
        );
    }

    public function testValidatePhoneNoDefaultCountryNoCountryField()
    {
        $this->setExpectedException('Propaganistas\LaravelPhone\Exceptions\NoValidCountryFoundException');

        // Validator with no country field or given country.
        $this->validator->make(
            ['field' => '016123456'],
            ['field' => 'phone']
        )->passes();

        // Validator with no country field or given country, wrong type.
        $this->validator->make(
            ['field' => '016123456'],
            ['field' => 'phone:mobile']
        )->passes();

        // Validator with no country field or given country, correct type.
        $this->validator->make(
            ['field' => '0499359308'],
            ['field' => 'phone:mobile']
        )->passes();

        // Validator with no country field or given country, correct type, faulty parameter.
        $this->validator->make(
            ['field' => '0499359308'],
            ['field' => 'phone:mobile,xyt']
        )->passes();
    }

    public function testValidatePhoneLenient()
    {
        // Validator with AU area code, lenient off
        $this->assertFalse($this->validator->make(
            ['field' => '88885555'],
            ['field' => 'phone:AU'])->passes()
        );

        // Validator with AU area code, lenient on
        $this->assertTrue($this->validator->make(
            ['field' => '88885555'],
            ['field' => 'phone:LENIENT,AU'])->passes()
        );

        // Validator with correct country field supplied, lenient on
        $this->assertTrue($this->validator->make(
            ['field' => '88885555', 'field_country' => 'AU'],
            ['field' => 'phone:LENIENT'])->passes()
        );

        // Validator with wrong country field supplied, lenient on
        $this->assertTrue($this->validator->make(
            ['field' => '88885555', 'field_country' => 'BE'],
            ['field' => 'phone:LENIENT'])->passes()
        );

        // Validator with no area code, lenient on
        $this->assertTrue($this->validator->make(
            ['field' => '+16502530000'],
            ['field' => 'phone:LENIENT'])->passes()
        );

        // Validator with US area code, lenient on
        $this->assertTrue($this->validator->make(
            ['field' => '+16502530000'],
            ['field' => 'phone:LENIENT,US'])->passes()
        );

        // Validator with no area code, lenient off
        $this->assertFalse($this->validator->make(
            ['field' => '6502530000'],
            ['field' => 'phone:LENIENT'])->passes()
        );

        // Validator with US area code, lenient on
        $this->assertTrue($this->validator->make(
            ['field' => '6502530000'],
            ['field' => 'phone:LENIENT,US'])->passes()
        );

        // Validator with US area code, lenient off
        $this->assertFalse($this->validator->make(
            ['field' => '2530000'],
            ['field' => 'phone:LENIENT'])->passes()
        );

        // Validator with US area code, lenient on
        $this->assertTrue($this->validator->make(
            ['field' => '2530000'],
            ['field' => 'phone:LENIENT,US'])->passes()
        );
    }

    public function testValidatePhoneFaultyParameters()
    {
        $this->setExpectedException('Propaganistas\LaravelPhone\Exceptions\InvalidParameterException');

        // Validator with given country, correct type, faulty parameter.
        $this->validator->make(
            ['field' => '016123456'],
            ['field' => 'phone:BE,mobile,xyz']
        )->passes();

        // Validator with country field, correct type, faulty parameter.
        $this->validator->make(
            ['field' => '016123456', 'field_country' => 'BE'],
            ['field' => 'phone:mobile,xyz']
        )->passes();
    }

    public function testValidatePhoneWithArrayInput()
    {
        if (LaravelPhoneServiceProvider::canUseDependentValidation()) {
            // Validator with correct country value.
            $this->assertTrue($this->validator->make(
                [
                    'container' => [
                        ['field' => '016123456'],
                        ['field' => '0499123456']
                    ]
                ],
                ['container.*.field' => 'phone:BE'])->passes()
            );

            // Validator with wrong country value.
            $this->assertFalse($this->validator->make(
                [
                    'container' => [
                        ['field' => '016123456'],
                        ['field' => '0499123456']
                    ]
                ],
                ['container.*.field' => 'phone:NL'])->passes()
            );

            // Validator with correct country value, one wrong input.
            $this->assertFalse($this->validator->make(
                [
                    'container' => [
                        ['field' => '01612'],
                        ['field' => '0499123456']
                    ]
                ],
                ['container.*.field' => 'phone:BE'])->passes()
            );

            // Validator with correct country value, one wrong input.
            $this->assertFalse($this->validator->make(
                [
                    'container' => [
                        ['field' => '016123456'],
                        ['field' => '049912']
                    ]
                ],
                ['container.*.field' => 'phone:BE'])->passes()
            );

            // Validator with correct country value.
            $this->assertTrue($this->validator->make(
                [
                    'container' => [
                        ['field' => '0477123456'],
                        ['field' => '0499123456']
                    ]
                ],
                ['container.*.field' => 'phone:BE,mobile'])->passes()
            );

            // Validator with correct country value, one input wrong type.
            $this->assertFalse($this->validator->make(
                [
                    'container' => [
                        ['field' => '016123456'],
                        ['field' => '0499123456']
                    ]
                ],
                ['container.*.field' => 'phone:BE,mobile'])->passes()
            );

            // Validator with correct country fields.
            $this->assertTrue($this->validator->make(
                [
                    'container' => [
                        ['field' => '016123456', 'field_country' => 'BE'],
                        ['field' => '6502530000', 'field_country' => 'US']
                    ]
                ],
                ['container.*.field' => 'phone'])->passes()
            );

            // Validator with correct country fields.
            $this->assertFalse($this->validator->make(
                [
                    'container' => [
                        ['field' => '016123456', 'field_country' => 'BE'],
                        ['field' => '6502530000', 'field_country' => 'BE']
                    ]
                ],
                ['container.*.field' => 'phone'])->passes()
            );

            // Validator with correct custom country fields.
            $this->assertTrue($this->validator->make(
                [
                    'container' => [
                        ['field' => '016123456', 'country_code' => 'BE'],
                        ['field' => '6502530000', 'country_code' => 'US']
                    ]
                ],
                ['container.*.field' => 'phone:container.*.country_code'])->passes()
            );

            // Validator with wrong custom country fields.
            $this->assertFalse($this->validator->make(
                [
                    'container' => [
                        ['field' => '016123456', 'country_code' => 'BE'],
                        ['field' => '6502530000', 'country_code' => 'BE']
                    ]
                ],
                ['container.*.field' => 'phone:container.*.country_code'])->passes()
            );
        }
    }

    public function testHelperFunction()
    {
        // Test landline number without format parameter.
        $actual = phone('016123456', 'BE');
        $expected = '+32 16 12 34 56';
        $this->assertEquals($expected, $actual);

        // Test landline number with format parameter.
        $actual = phone('016123456', 'BE', PhoneNumberFormat::NATIONAL);
        $expected = '016 12 34 56';
        $this->assertEquals($expected, $actual);

        // Test fetching of util.
        $this->assertTrue(phone() instanceof PhoneNumberUtil);
    }
}
