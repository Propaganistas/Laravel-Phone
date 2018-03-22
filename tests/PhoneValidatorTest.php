<?php namespace Propaganistas\LaravelPhone\Tests;

use libphonenumber\PhoneNumberType;
use Propaganistas\LaravelPhone\PhoneServiceProvider;
use Propaganistas\LaravelPhone\Rules\Phone as Rule;

class PhoneValidatorTest extends TestCase
{
    protected $validator;

    public function setUp()
    {
        parent::setUp();

        $this->validator = $this->app['validator'];
    }

    /** @test */
    public function it_validates_with_default_countries_without_type()
    {
        // Validator with correct country field.
        $this->assertTrue($this->validator->make(
            ['field' => '012345678'],
            ['field' => 'phone:BE'])->passes()
        );

        // Validator with wrong country value.
        $this->assertFalse($this->validator->make(
            ['field' => '012345678'],
            ['field' => 'phone:NL'])->passes()
        );

        // Validator with multiple country values, one correct.
        $this->assertTrue($this->validator->make(
            ['field' => '012345678'],
            ['field' => 'phone:BE,NL'])->passes()
        );

        // Validator with multiple country values, value correct for second country in list.
        $this->assertTrue($this->validator->make(
            ['field' => '012345678'],
            ['field' => 'phone:NL,BE'])->passes()
        );

        // Validator with multiple wrong country values.
        $this->assertFalse($this->validator->make(
            ['field' => '012345678'],
            ['field' => 'phone:DE,NL'])->passes()
        );
    }

    /** @test */
    public function it_validates_with_country_field_without_type()
    {
        // Validator with correct country field supplied.
        $this->assertTrue($this->validator->make(
            ['field' => '012345678', 'field_country' => 'BE'],
            ['field' => 'phone'])->passes()
        );

        // Validator with wrong country field supplied.
        $this->assertFalse($this->validator->make(
            ['field' => '012345678', 'field_country' => 'NL'],
            ['field' => 'phone'])->passes()
        );
    }
/*
    /** @test */
    public function it_validates_with_default_countries_with_type()
    {
        // Validator with correct country value, correct type.
        $this->assertTrue($this->validator->make(
            ['field' => '0470123456'],
            ['field' => 'phone:BE,mobile'])->passes()
        );

        // Validator with correct country value, wrong type.
        $this->assertFalse($this->validator->make(
            ['field' => '012345678'],
            ['field' => 'phone:BE,mobile'])->passes()
        );

        // Validator with wrong country value, correct type.
        $this->assertFalse($this->validator->make(
            ['field' => '0470123456'],
            ['field' => 'phone:NL,mobile'])->passes()
        );

        // Validator with wrong country value, wrong type.
        $this->assertFalse($this->validator->make(
            ['field' => '012345678'],
            ['field' => 'phone:NL,mobile'])->passes()
        );

        // Validator with multiple country values, one correct, correct type.
        $this->assertTrue($this->validator->make(
            ['field' => '0470123456'],
            ['field' => 'phone:BE,NL,mobile'])->passes()
        );

        // Validator with multiple country values, one correct, wrong type.
        $this->assertFalse($this->validator->make(
            ['field' => '012345678'],
            ['field' => 'phone:BE,NL,mobile'])->passes()
        );

        // Validator with multiple country values, none correct, correct type.
        $this->assertFalse($this->validator->make(
            ['field' => '0470123456'],
            ['field' => 'phone:DE,NL,mobile'])->passes()
        );

        // Validator with multiple country values, none correct, wrong type.
        $this->assertFalse($this->validator->make(
            ['field' => '012345678'],
            ['field' => 'phone:DE,NL,mobile'])->passes()
        );
    }

    /** @test */
    public function it_validates_with_country_field_with_type()
    {
        // Validator with correct country field supplied, correct type.
        $this->assertTrue($this->validator->make(
            ['field' => '0470123456', 'field_country' => 'BE'],
            ['field' => 'phone:mobile'])->passes()
        );

        // Validator with correct country field supplied, wrong type.
        $this->assertFalse($this->validator->make(
            ['field' => '012345678', 'field_country' => 'BE'],
            ['field' => 'phone:mobile'])->passes()
        );

        // Validator with wrong country field supplied, correct type.
        $this->assertFalse($this->validator->make(
            ['field' => '0470123456', 'field_country' => 'NL'],
            ['field' => 'phone:mobile'])->passes()
        );

        // Validator with wrong country field supplied, wrong type.
        $this->assertFalse($this->validator->make(
            ['field' => '012345678', 'field_country' => 'NL'],
            ['field' => 'phone:mobile'])->passes()
        );
    }

    /** @test */
    public function it_validates_custom_country_field()
    {
        // Validator with correct country field supplied, correct type.
        $this->assertTrue($this->validator->make(
            ['field' => '0470123456', 'country_code' => 'BE'],
            ['field' => 'phone:mobile,country_code'])->passes()
        );

        // Validator with correct country field supplied, wrong type.
        $this->assertFalse($this->validator->make(
            ['field' => '012345678', 'country_code' => 'BE'],
            ['field' => 'phone:mobile,country_code'])->passes()
        );

        // Validator with wrong country field supplied, correct type.
        $this->assertFalse($this->validator->make(
            ['field' => '0470123456', 'country_code' => 'NL'],
            ['field' => 'phone:mobile,country_code'])->passes()
        );

        // Validator with wrong country field supplied, wrong type.
        $this->assertFalse($this->validator->make(
            ['field' => '012345678', 'country_code' => 'NL'],
            ['field' => 'phone:mobile,country_code'])->passes()
        );
    }

    /** @test */
    public function it_validates_with_automatic_detection()
    {
        // Validator with correct international input.
        $this->assertTrue($this->validator->make(
            ['field' => '+3212345678'],
            ['field' => 'phone:AUTO'])->passes()
        );

        // Validator with wrong international input.
        $this->assertFalse($this->validator->make(
            ['field' => '003212345678'],
            ['field' => 'phone:AUTO'])->passes()
        );

        // Validator with wrong international input.
        $this->assertFalse($this->validator->make(
            ['field' => '+321234'],
            ['field' => 'phone:AUTO'])->passes()
        );

        // Validator with wrong international input but correct default country.
        $this->assertTrue($this->validator->make(
            ['field' => '012345678'],
            ['field' => 'phone:AUTO,NL,BE'])->passes()
        );

        // Validator with wrong international input and wrong default country.
        $this->assertFalse($this->validator->make(
            ['field' => '012345678'],
            ['field' => 'phone:AUTO,DE,NL'])->passes()
        );
    }

    /** @test */
    public function it_validates_without_countries()
    {
        // Validator with no country field or given country.
        $this->assertFalse($this->validator->make(
            ['field' => '012345678'],
            ['field' => 'phone']
        )->passes());

        // Validator with no country field or given country, wrong type.
        $this->assertFalse($this->validator->make(
            ['field' => '012345678'],
            ['field' => 'phone:mobile']
        )->passes());

        // Validator with no country field or given country, correct type.
        $this->assertFalse($this->validator->make(
            ['field' => '0470123456'],
            ['field' => 'phone:mobile']
        )->passes());
    }

    /** @test */
    public function it_validates_with_auto_and_country_field_and_fallback_country()
    {
        // Validator with correct country in custom field.
        $this->assertTrue($this->validator->make(
            ['field' => '012345678', 'country' => 'BE'],
            ['field' => 'phone:AUTO,US,country']
        )->passes());

        // Validator with correct country as fallback country.
        $this->assertTrue($this->validator->make(
            ['field' => '012345678', 'country' => 'US'],
            ['field' => 'phone:AUTO,BE,country']
        )->passes());

        // Validator with wrong field value and fallback country, but internationally formatted.
        $this->assertTrue($this->validator->make(
            ['field' => '+3212345678', 'country' => 'US'],
            ['field' => 'phone:AUTO,CH,country']
        )->passes());

        // Validator with wrong field value and fallback country, and not internationally formatted.
        $this->assertFalse($this->validator->make(
            ['field' => '012345678', 'country' => 'US'],
            ['field' => 'phone:AUTO,CH,country']
        )->passes());
    }

    /**
     * @test
     *
     * @expectedException \Propaganistas\LaravelPhone\Exceptions\InvalidParameterException
     * @expectedExceptionMessage xyz,abc
     */
    public function it_throws_an_exception_for_invalid_parameters()
    {
        $this->validator->make(
            ['field' => '0470123456'],
            ['field' => 'phone:BE,xyz,mobile,abc']
        )->passes();
    }

    /**
     * @test
     *
     * @expectedException \Propaganistas\LaravelPhone\Exceptions\InvalidParameterException
     * @expectedExceptionMessage mobile
     */
    public function it_throws_an_exception_for_ambiguous_parameters()
    {
        $this->validator->make(
            ['mobile' => '0470123456', 'mobile_country' => 'BE'],
            ['mobile' => 'phone:mobile']
        )->passes();
    }

    /** @test */
    public function it_doesnt_throw_an_exception_for_an_invalid_country_field_value()
    {
        $this->assertFalse($this->validator->make(
            ['field' => '012345678', 'field_country' => 'foo'],
            ['field' => 'phone']
        )->passes());
    }

    /** @test */
    public function it_validates_lenient()
    {
        // Validator with AU area code, lenient off
        $this->assertFalse($this->validator->make(
            ['field' => '12345678'],
            ['field' => 'phone:AU'])->passes()
        );

        // Validator with AU area code, lenient on
        $this->assertTrue($this->validator->make(
            ['field' => '12345678'],
            ['field' => 'phone:LENIENT,AU'])->passes()
        );

        // Validator with correct country field supplied, lenient on
        $this->assertTrue($this->validator->make(
            ['field' => '12345678', 'field_country' => 'AU'],
            ['field' => 'phone:LENIENT'])->passes()
        );

        // Validator with wrong country field supplied, lenient on
        $this->assertTrue($this->validator->make(
            ['field' => '12345678', 'field_country' => 'BE'],
            ['field' => 'phone:LENIENT'])->passes()
        );

        // Validator with no area code, lenient on
        $this->assertTrue($this->validator->make(
            ['field' => '+12015550123'],
            ['field' => 'phone:LENIENT'])->passes()
        );

        // Validator with US area code, lenient on
        $this->assertTrue($this->validator->make(
            ['field' => '+16502530000'],
            ['field' => 'phone:LENIENT,US'])->passes()
        );

        // Validator with no area code, lenient off
        $this->assertFalse($this->validator->make(
            ['field' => '2015550123'],
            ['field' => 'phone:LENIENT'])->passes()
        );

        // Validator with US area code, lenient on
        $this->assertTrue($this->validator->make(
            ['field' => '2015550123'],
            ['field' => 'phone:LENIENT,US'])->passes()
        );

        // Validator with US area code, lenient off
        $this->assertFalse($this->validator->make(
            ['field' => '5550123'],
            ['field' => 'phone:LENIENT'])->passes()
        );

        // Validator with US area code, lenient on
        $this->assertTrue($this->validator->make(
            ['field' => '5550123'],
            ['field' => 'phone:LENIENT,US'])->passes()
        );
    }

    /** @test */
    public function it_validates_array_input()
    {
        if (PhoneServiceProvider::canUseDependentValidation()) {
            // Validator with correct country value.
            $this->assertTrue($this->validator->make(
                [
                    'container' => [
                        ['field' => '012345678'],
                        ['field' => '0470123456'],
                    ],
                ],
                ['container.*.field' => 'phone:BE'])->passes()
            );

            // Validator with wrong country value.
            $this->assertFalse($this->validator->make(
                [
                    'container' => [
                        ['field' => '012345678'],
                        ['field' => '0470123456'],
                    ],
                ],
                ['container.*.field' => 'phone:NL'])->passes()
            );

            // Validator with correct country value, one wrong input.
            $this->assertFalse($this->validator->make(
                [
                    'container' => [
                        ['field' => '01234'],
                        ['field' => '0470123456'],
                    ],
                ],
                ['container.*.field' => 'phone:BE'])->passes()
            );

            // Validator with correct country value, one wrong input.
            $this->assertFalse($this->validator->make(
                [
                    'container' => [
                        ['field' => '012345678'],
                        ['field' => '047012'],
                    ],
                ],
                ['container.*.field' => 'phone:BE'])->passes()
            );

            // Validator with correct country value.
            $this->assertTrue($this->validator->make(
                [
                    'container' => [
                        ['field' => '0470123456'],
                        ['field' => '0471123456'],
                    ],
                ],
                ['container.*.field' => 'phone:BE,mobile'])->passes()
            );

            // Validator with correct country value, one input wrong type.
            $this->assertFalse($this->validator->make(
                [
                    'container' => [
                        ['field' => '012345678'],
                        ['field' => '0470123456'],
                    ],
                ],
                ['container.*.field' => 'phone:BE,mobile'])->passes()
            );

            // Validator with correct country fields.
            $this->assertTrue($this->validator->make(
                [
                    'container' => [
                        ['field' => '012345678', 'field_country' => 'BE'],
                        ['field' => '2015550123', 'field_country' => 'US'],
                    ],
                ],
                ['container.*.field' => 'phone'])->passes()
            );

            // Validator with correct country fields.
            $this->assertFalse($this->validator->make(
                [
                    'container' => [
                        ['field' => '012345678', 'field_country' => 'BE'],
                        ['field' => '2015550123', 'field_country' => 'BE'],
                    ],
                ],
                ['container.*.field' => 'phone'])->passes()
            );

            // Validator with correct custom country fields.
            $this->assertTrue($this->validator->make(
                [
                    'container' => [
                        ['field' => '012345678', 'country_code' => 'BE'],
                        ['field' => '2015550123', 'country_code' => 'US'],
                    ],
                ],
                ['container.*.field' => 'phone:container.*.country_code'])->passes()
            );

            // Validator with wrong custom country fields.
            $this->assertFalse($this->validator->make(
                [
                    'container' => [
                        ['field' => '012345678', 'country_code' => 'BE'],
                        ['field' => '2015550123', 'country_code' => 'BE'],
                    ],
                ],
                ['container.*.field' => 'phone:container.*.country_code'])->passes()
            );
        }
    }

    /** @test */
    public function it_has_a_rule_class()
    {
        $actual = new Rule;
        $expected = 'phone';
        $this->assertEquals($expected, $actual);

        $actual = with(new Rule)->mobile();
        $expected = 'phone:1';
        $this->assertEquals($expected, (string) $actual);

        $actual = with(new Rule)->mobile()->fixedLine();
        $expected = 'phone:1,0';
        $this->assertEquals($expected, (string) $actual);

        $actual = with(new Rule)->country('BE')->country('AU','US')->country(['CH','FR']);
        $expected = 'phone:BE,AU,US,CH,FR';
        $this->assertEquals($expected, (string) $actual);

        $actual = with(new Rule)->detect();
        $expected = 'phone:AUTO';
        $this->assertEquals($expected, (string) $actual);

        $actual = with(new Rule)->lenient();
        $expected = 'phone:LENIENT';
        $this->assertEquals($expected, (string) $actual);

        $actual = with(new Rule)->detect()->lenient()->type('toll_free')->type(PhoneNumberType::VOIP)->country('BE')->countryField('my_field');
        $expected = 'phone:BE,toll_free,6,my_field,AUTO,LENIENT';
        $this->assertEquals($expected, (string) $actual);
    }

    /** @test */
    public function it_validates_with_stringified_type_constant()
    {
        $this->assertTrue($this->validator->make(
            ['field' => '0470123456'],
            ['field' => 'phone:BE,' . PhoneNumberType::MOBILE])->passes()
        );
    }

    /** @test */
    public function it_prevents_parameter_hijacking_through_the_country_field()
    {
        $this->assertFalse($this->validator->make(
            ['field' => '0470123456', 'field_country' => 'mobile'],
            ['field' => 'phone:BE,fixed_line'])->passes()
        );
    }

    /** @test */
    public function it_accepts_mixed_case_parameters()
    {
        $this->assertTrue($this->validator->make(
            ['field' => '+32470123456'],
            ['field' => 'phone:aUtO,mObIlE'])->passes()
        );

        $this->assertTrue($this->validator->make(
            ['field' => '0470123456'],
            ['field' => 'phone:bE,mObIlE'])->passes()
        );

        $this->assertFalse($this->validator->make(
            ['field' => '0470123456'],
            ['field' => 'phone:AuTo,MoBiLe'])->passes()
        );
    }
}
