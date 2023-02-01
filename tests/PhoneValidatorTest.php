<?php

namespace Propaganistas\LaravelPhone\Tests;

use Illuminate\Validation\Validator;
use libphonenumber\PhoneNumberType;

class PhoneValidatorTest extends TestCase
{
    protected function validate(array $data, array $rules): Validator
    {
        return $this->app['validator']->make($data, $rules);
    }

    /** @test */
    public function it_validates_with_explicit_countries()
    {
        $this->assertTrue($this->validate(
            ['field' => '012345678'],
            ['field' => 'phone:BE']
        )->passes());

        $this->assertTrue($this->validate(
            ['field' => '012345678'],
            ['field' => 'phone:NL,BE,US']
        )->passes());

        $this->assertFalse($this->validate(
            ['field' => '012345678'],
            ['field' => 'phone:NL']
        )->passes());

        $this->assertFalse($this->validate(
            ['field' => '012345678'],
            ['field' => 'phone:DE,NL,US']
        )->passes());
    }

    /** @test */
    public function it_validates_with_implicit_country_field()
    {
        $this->assertTrue($this->validate(
            ['field' => '012345678', 'field_country' => 'BE'],
            ['field' => 'phone']
        )->passes());

        $this->assertFalse($this->validate(
            ['field' => '012345678', 'field_country' => 'NL'],
            ['field' => 'phone']
        )->passes());
    }

    /** @test */
    public function it_validates_with_custom_country_field()
    {
        $this->assertTrue($this->validate(
            ['field' => '012345678', 'some_country' => 'BE'],
            ['field' => 'phone:some_country']
        )->passes());

        $this->assertFalse($this->validate(
            ['field' => '012345678', 'some_country' => 'NL'],
            ['field' => 'phone:some_country']
        )->passes());
    }

    /** @test */
    public function it_validates_with_explicit_countries_and_implicit_country_field_combined()
    {
        $this->assertTrue($this->validate(
            ['field' => '012345678', 'field_country' => 'BE'],
            ['field' => 'phone:NL,field_country']
        )->passes());

        $this->assertTrue($this->validate(
            ['field' => '012345678', 'field_country' => 'NL'],
            ['field' => 'phone:BE,field_country']
        )->passes());
    }

    /** @test */
    public function it_validates_with_explicit_countries_and_custom_country_field_combined()
    {
        $this->assertTrue($this->validate(
            ['field' => '012345678', 'some_country' => 'BE'],
            ['field' => 'phone:NL,some_country']
        )->passes());

        $this->assertTrue($this->validate(
            ['field' => '012345678', 'some_country' => 'NL'],
            ['field' => 'phone:BE,some_country']
        )->passes());
    }

    /** @test */
    public function it_validates_with_custom_country_field_taking_precedence_over_implicit_country_field()
    {
        $this->assertTrue($this->validate(
            ['field' => '012345678', 'field_country' => 'NL', 'some_country' => 'BE'],
            ['field' => 'phone:some_country']
        )->passes());

        $this->assertFalse($this->validate(
            ['field' => '012345678', 'field_country' => 'BE', 'some_country' => 'NL'],
            ['field' => 'phone:some_country']
        )->passes());
    }

    /** @test */
    public function it_validates_without_countries()
    {
        $this->assertTrue($this->validate(
            ['field' => '+3212345678'],
            ['field' => 'phone']
        )->passes());

        $this->assertFalse($this->validate(
            ['field' => '003212345678'],
            ['field' => 'phone']
        )->passes());

        $this->assertFalse($this->validate(
            ['field' => '+321234'],
            ['field' => 'phone']
        )->passes());
    }

    /** @test */
    public function it_validates_in_international_mode()
    {
        $this->assertFalse($this->validate(
            ['field' => '+3212345678'],
            ['field' => 'phone:NL']
        )->passes());

        $this->assertTrue($this->validate(
            ['field' => '+3212345678'],
            ['field' => 'phone:international,NL']
        )->passes());

        $this->assertFalse($this->validate(
            ['field' => '012345678'],
            ['field' => 'phone:international,NL']
        )->passes());

        $this->assertTrue($this->validate(
            ['field' => '012345678'],
            ['field' => 'phone:international,BE']
        )->passes());
    }

    /** @test */
    public function it_gracefully_ignores_invalid_country_field_value()
    {
        $this->assertFalse($this->validate(
            ['field' => '012345678', 'field_country' => 'foo'],
            ['field' => 'phone']
        )->passes());

        $this->assertFalse($this->validate(
            ['field' => '012345678', 'some_country' => 'foo'],
            ['field' => 'phone:some_country']
        )->passes());
    }

    /** @test */
    public function it_gracefully_ignores_invalid_parameters()
    {
        $this->assertFalse($this->validate(
            ['field' => '0470123456'],
            ['field' => 'phone:xyz,foo']
        )->passes());
    }

    /** @test */
    public function it_validates_in_lenient_mode()
    {
        $this->assertFalse($this->validate(
            ['field' => '12345678'],
            ['field' => 'phone:AU']
        )->passes());

        $this->assertTrue($this->validate(
            ['field' => '12345678'],
            ['field' => 'phone:lenient,AU']
        )->passes());

        $this->assertFalse($this->validate(
            ['field' => '+49(0)12-44 614038'],
            ['field' => 'phone']
        )->passes());

        $this->assertTrue($this->validate(
            ['field' => '+49(0)12-44 614038'],
            ['field' => 'phone:lenient']
        )->passes());
    }

    /** @test */
    public function it_validates_array_input_with_explicit_countries()
    {
        $errors = $this->validate(
            [
                'container' => [
                    ['field' => '012345678'],
                    ['field' => '0470123456'],
                ],
            ],
            ['container.*.field' => 'phone:BE']
        )->errors();

        $this->assertEquals([], $errors->keys());

        $errors = $this->validate(
            [
                'container' => [
                    ['field' => '012345678'],
                    ['field' => '2015550123'],
                ],
            ],
            ['container.*.field' => 'phone:US']
        )->errors();

        $this->assertEquals(['container.0.field'], $errors->keys());

        $errors = $this->validate(
            [
                'container' => [
                    ['field' => '012345678'],
                    ['field' => '0470123456'],
                ],
            ],
            ['container.*.field' => 'phone:US']
        )->errors();

        $this->assertEquals(['container.0.field', 'container.1.field'], $errors->keys());
    }

    /** @test */
    public function it_validates_array_input_with_implicity_country_fields()
    {
        $errors = $this->validate(
            [
                'container' => [
                    ['field' => '012345678', 'field_country' => 'BE'],
                    ['field' => '0470123456', 'field_country' => 'BE'],
                ],
            ],
            ['container.*.field' => 'phone']
        )->errors();

        $this->assertEquals([], $errors->keys());

        $errors = $this->validate(
            [
                'container' => [
                    ['field' => '012345678', 'field_country' => 'US'],
                    ['field' => '2015550123', 'field_country' => 'US'],
                    ['field' => '012345678', 'field_country' => 'BE'],
                ],
            ],
            ['container.*.field' => 'phone']
        )->errors();

        $this->assertEquals(['container.0.field'], $errors->keys());
    }

    /** @test */
    public function it_validates_array_input_with_custom_country_fields()
    {
        $errors = $this->validate(
            [
                'container' => [
                    ['field' => '012345678'],
                    ['field' => '0470123456'],
                ],
                'some_country' => 'BE',
            ],
            ['container.*.field' => 'phone:some_country']
        )->errors();

        $this->assertEquals([], $errors->keys());

        $errors = $this->validate(
            [
                'container' => [
                    ['field' => '012345678'],
                    ['field' => '2015550123'],
                ],
                'some_country' => 'US',
            ],
            ['container.*.field' => 'phone:some_country']
        )->errors();

        $this->assertEquals(['container.0.field'], $errors->keys());
    }

    /** @test */
    public function it_validates_type()
    {
        $this->assertTrue($this->validate(
            ['field' => '+32470123456'],
            ['field' => 'phone:'.PhoneNumberType::MOBILE]
        )->passes());

        $this->assertFalse($this->validate(
            ['field' => '+3212345678'],
            ['field' => 'phone:'.PhoneNumberType::MOBILE]
        )->passes());
    }

    /** @test */
    public function it_validates_type_and_explicit_country_combined()
    {
        $this->assertTrue($this->validate(
            ['field' => '0470123456'],
            ['field' => 'phone:BE,'.PhoneNumberType::MOBILE]
        )->passes());

        $this->assertFalse($this->validate(
            ['field' => '012345678'],
            ['field' => 'phone:BE,'.PhoneNumberType::MOBILE]
        )->passes());

        $this->assertFalse($this->validate(
            ['field' => '0470123456'],
            ['field' => 'phone:NL,'.PhoneNumberType::MOBILE]
        )->passes());
    }

    /** @test */
    public function it_validates_type_and_implicit_country_field_combined()
    {
        $this->assertTrue($this->validate(
            ['field' => '0470123456', 'field_country' => 'BE'],
            ['field' => 'phone:'.PhoneNumberType::MOBILE]
        )->passes());

        $this->assertFalse($this->validate(
            ['field' => '012345678', 'field_country' => 'BE'],
            ['field' => 'phone:'.PhoneNumberType::MOBILE]
        )->passes());

        $this->assertFalse($this->validate(
            ['field' => '0470123456', 'field_country' => 'NL'],
            ['field' => 'phone:'.PhoneNumberType::MOBILE]
        )->passes());
    }

    /** @test */
    public function it_validates_type_and_custom_country_field_combined()
    {
        $this->assertTrue($this->validate(
            ['field' => '0470123456', 'some_country' => 'BE'],
            ['field' => 'phone:some_country,'.PhoneNumberType::MOBILE]
        )->passes());

        $this->assertFalse($this->validate(
            ['field' => '012345678', 'some_country' => 'BE'],
            ['field' => 'phone:some_country,'.PhoneNumberType::MOBILE]
        )->passes());

        $this->assertFalse($this->validate(
            ['field' => '0470123456', 'some_country' => 'NL'],
            ['field' => 'phone:some_country,'.PhoneNumberType::MOBILE]
        )->passes());
    }

    /** @test */
    public function it_validates_type_as_string()
    {
        $this->assertTrue($this->validate(
            ['field' => '+32470123456'],
            ['field' => 'phone:mobile']
        )->passes());

        $this->assertFalse($this->validate(
            ['field' => '+3212345678'],
            ['field' => 'phone:mobile']
        )->passes());
    }

    /** @test */
    public function it_prevents_parameter_hijacking_through_the_country_field()
    {
        $this->assertFalse($this->validate(
            ['field' => '0470123456', 'field_country' => 'mobile'],
            ['field' => 'phone:BE,fixed_line']
        )->passes());
    }

    /** @test */
    public function it_accepts_mixed_case_parameters()
    {
        $this->assertTrue($this->validate(
            ['field' => '+32470123456'],
            ['field' => 'phone:aUtO,mObIlE']
        )->passes());

        $this->assertTrue($this->validate(
            ['field' => '0470123456'],
            ['field' => 'phone:bE,mObIlE']
        )->passes());

        $this->assertFalse($this->validate(
            ['field' => '0470123456'],
            ['field' => 'phone:AuTo,MoBiLe']
        )->passes());
    }

    /** @test */
    public function it_validates_explicit_lowercase_countries()
    {
        $this->assertTrue($this->validate(
            ['field' => '0470123456'],
            ['field' => 'phone:be']
        )->passes());

        $this->assertFalse($this->validate(
            ['field' => '0470123456'],
            ['field' => 'phone:us']
        )->passes());
    }

    /** @test */
    public function it_validates_implicit_lowercase_country_field()
    {
        $this->assertTrue($this->validate(
            ['field' => '0470123456', 'field_country' => 'be'],
            ['field' => 'phone']
        )->passes());

        $this->assertFalse($this->validate(
            ['field' => '0470123456', 'field_country' => 'us'],
            ['field' => 'phone']
        )->passes());
    }

    /** @test */
    public function it_validates_custom_lowercase_country_field()
    {
        $this->assertTrue($this->validate(
            ['field' => '0470123456', 'some_country' => 'be'],
            ['field' => 'phone:some_country']
        )->passes());

        $this->assertFalse($this->validate(
            ['field' => '0470123456', 'some_country' => 'us'],
            ['field' => 'phone:some_country']
        )->passes());
    }

    /** @test */
    public function it_copes_with_nullable_validation_rule()
    {
        $this->assertTrue($this->validate(
            ['field' => null],
            ['field' => ['nullable', 'phone:BE']]
        )->passes());
    }

    /** @test */
    public function it_validates_libphonenumber_specific_regions_as_country()
    {
        $this->assertTrue($this->validate(
            ['field' => '+247501234'],
            ['field' => ['phone:AC']]
        )->passes());
    }
}
