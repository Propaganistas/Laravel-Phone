<?php

namespace Propaganistas\LaravelPhone\Tests;

use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;
use libphonenumber\PhoneNumberType;
use PHPUnit\Framework\Attributes\Test;
use Propaganistas\LaravelPhone\Exceptions\IncompatibleTypesException;
use Propaganistas\LaravelPhone\Rules\Phone;

class ValidatorTest extends TestCase
{
    protected function validate(array $data, array $rules): Validator
    {
        return $this->app['validator']->make($data, $rules);
    }

    #[Test]
    public function it_validates_without_parameters()
    {
        $this->assertTrue($this->validate(
            ['field' => '+3212345678'],
            ['field' => new Phone]
        )->passes());

        $this->assertFalse($this->validate(
            ['field' => '003212345678'],
            ['field' => new Phone]
        )->passes());

        $this->assertFalse($this->validate(
            ['field' => '+321234'],
            ['field' => new Phone]
        )->passes());
    }

    #[Test]
    public function it_validates_with_explicit_countries()
    {
        $this->assertTrue($this->validate(
            ['field' => '012345678'],
            ['field' => (new Phone)->country('BE')]
        )->passes());

        $this->assertTrue($this->validate(
            ['field' => '012345678'],
            ['field' => (new Phone)->country(['NL', 'BE', 'US'])]
        )->passes());

        $this->assertFalse($this->validate(
            ['field' => '012345678'],
            ['field' => (new Phone)->country('NL')]
        )->passes());

        $this->assertFalse($this->validate(
            ['field' => '012345678'],
            ['field' => (new Phone)->country(['DE', 'NL', 'US'])]
        )->passes());
    }

    #[Test]
    public function it_validates_countries_case_insensitive()
    {
        $this->assertTrue($this->validate(
            ['field' => '012345678'],
            ['field' => (new Phone)->country('bE')]
        )->passes());

        $this->assertTrue($this->validate(
            ['field' => '012345678'],
            ['field' => (new Phone)->country(['Be'])]
        )->passes());
    }

    #[Test]
    public function it_validates_with_implicit_country_field()
    {
        $this->assertTrue($this->validate(
            ['field' => '012345678', 'field_country' => 'BE'],
            ['field' => new Phone]
        )->passes());

        $this->assertFalse($this->validate(
            ['field' => '012345678', 'field_country' => 'NL'],
            ['field' => new Phone]
        )->passes());
    }

    #[Test]
    public function it_validates_with_custom_country_field()
    {
        $this->assertTrue($this->validate(
            ['field' => '012345678', 'some_country' => 'BE'],
            ['field' => (new Phone)->countryField('some_country')],
        )->passes());

        $this->assertFalse($this->validate(
            ['field' => '012345678', 'some_country' => 'NL'],
            ['field' => (new Phone)->countryField('some_country')]
        )->passes());
    }

    #[Test]
    public function it_validates_country_field_case_insensitive()
    {
        $this->assertTrue($this->validate(
            ['field' => '012345678', 'field_country' => 'Be'],
            ['field' => new Phone]
        )->passes());

        $this->assertTrue($this->validate(
            ['field' => '012345678', 'field_country' => 'bE'],
            ['field' => new Phone]
        )->passes());
    }

    #[Test]
    public function it_validates_with_explicit_countries_and_implicit_country_field_combined()
    {
        $this->assertTrue($this->validate(
            ['field' => '012345678', 'field_country' => 'BE'],
            ['field' => (new Phone)->country('NL')->countryField('field_country')]
        )->passes());

        $this->assertTrue($this->validate(
            ['field' => '012345678', 'field_country' => 'NL'],
            ['field' => (new Phone)->country('BE')->countryField('field_country')]
        )->passes());
    }

    #[Test]
    public function it_validates_with_explicit_countries_and_custom_country_field_combined()
    {
        $this->assertTrue($this->validate(
            ['field' => '012345678', 'some_country' => 'BE'],
            ['field' => (new Phone)->country('NL')->countryField('some_country')]
        )->passes());

        $this->assertTrue($this->validate(
            ['field' => '012345678', 'some_country' => 'NL'],
            ['field' => (new Phone)->country('BE')->countryField('some_country')]
        )->passes());
    }

    #[Test]
    public function it_validates_with_custom_country_field_taking_precedence_over_implicit_country_field()
    {
        $this->assertTrue($this->validate(
            ['field' => '012345678', 'field_country' => 'NL', 'some_country' => 'BE'],
            ['field' => (new Phone)->countryField('some_country')]
        )->passes());

        $this->assertFalse($this->validate(
            ['field' => '012345678', 'field_country' => 'BE', 'some_country' => 'NL'],
            ['field' => (new Phone)->countryField('some_country')]
        )->passes());
    }

    #[Test]
    public function it_validates_in_international_mode()
    {
        $this->assertTrue($this->validate(
            ['field' => '+3212345678'],
            ['field' => (new Phone)->international()]
        )->passes());

        $this->assertFalse($this->validate(
            ['field' => '+3212345678'],
            ['field' => (new Phone)->country('NL')]
        )->passes());

        $this->assertTrue($this->validate(
            ['field' => '+3212345678'],
            ['field' => (new Phone)->country('NL')->international()]
        )->passes());

        $this->assertFalse($this->validate(
            ['field' => '012345678'],
            ['field' => (new Phone)->country('NL')->international()]
        )->passes());

        $this->assertTrue($this->validate(
            ['field' => '012345678'],
            ['field' => (new Phone)->country('BE')->international()]
        )->passes());
    }

    #[Test]
    public function it_validates_non_parseable_international_looking_numbers()
    {
        $this->assertFalse($this->validate(
            ['field' => '+12345678901234'],
            ['field' => (new Phone)->international()]
        )->passes());
    }

    #[Test]
    public function it_validates_in_lenient_mode()
    {
        $this->assertFalse($this->validate(
            ['field' => '12345678'],
            ['field' => (new Phone)->country('AU')]
        )->passes());

        $this->assertTrue($this->validate(
            ['field' => '12345678'],
            ['field' => (new Phone)->country('AU')->lenient()]
        )->passes());

        $this->assertFalse($this->validate(
            ['field' => '+49(0)12-44 614038'],
            ['field' => new Phone]
        )->passes());

        $this->assertTrue($this->validate(
            ['field' => '+49(0)12-44 614038'],
            ['field' => (new Phone)->lenient()]
        )->passes());
    }

    #[Test]
    public function it_validates_array_input_with_explicit_countries()
    {
        $errors = $this->validate(
            [
                'container' => [
                    ['field' => '012345678'],
                    ['field' => '0470123456'],
                ],
            ],
            ['container.*.field' => (new Phone)->country('BE')]
        )->errors();

        $this->assertEquals([], $errors->keys());

        $errors = $this->validate(
            [
                'container' => [
                    ['field' => '012345678'],
                    ['field' => '2015550123'],
                ],
            ],
            ['container.*.field' => (new Phone)->country('US')]
        )->errors();

        $this->assertEquals(['container.0.field'], $errors->keys());

        $errors = $this->validate(
            [
                'container' => [
                    ['field' => '012345678'],
                    ['field' => '0470123456'],
                ],
            ],
            ['container.*.field' => (new Phone)->country('US')]
        )->errors();

        $this->assertEquals(['container.0.field', 'container.1.field'], $errors->keys());
    }

    #[Test]
    public function it_validates_array_input_with_implicity_country_fields()
    {
        $errors = $this->validate(
            [
                'container' => [
                    ['field' => '012345678', 'field_country' => 'BE'],
                    ['field' => '0470123456', 'field_country' => 'BE'],
                ],
            ],
            ['container.*.field' => new Phone]
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
            ['container.*.field' => new Phone]
        )->errors();

        $this->assertEquals(['container.0.field'], $errors->keys());
    }

    #[Test]
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
            ['container.*.field' => (new Phone)->countryField('some_country')]
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
            ['container.*.field' => (new Phone)->countryField('some_country')]
        )->errors();

        $this->assertEquals(['container.0.field'], $errors->keys());
    }

    #[Test]
    public function it_validates_type()
    {
        $this->assertTrue($this->validate(
            ['field' => '+32470123456'],
            ['field' => (new Phone)->type(PhoneNumberType::MOBILE)]
        )->passes());

        $this->assertFalse($this->validate(
            ['field' => '+3212345678'],
            ['field' => (new Phone)->type(PhoneNumberType::MOBILE)]
        )->passes());
    }

    #[Test]
    public function it_validates_type_and_explicit_country_combined()
    {
        $this->assertTrue($this->validate(
            ['field' => '0470123456'],
            ['field' => (new Phone)->type(PhoneNumberType::MOBILE)->country('BE')]
        )->passes());

        $this->assertFalse($this->validate(
            ['field' => '012345678'],
            ['field' => (new Phone)->type(PhoneNumberType::MOBILE)->country('BE')]
        )->passes());

        $this->assertFalse($this->validate(
            ['field' => '0470123456'],
            ['field' => (new Phone)->type(PhoneNumberType::MOBILE)->country('NL')]
        )->passes());
    }

    #[Test]
    public function it_validates_type_and_implicit_country_field_combined()
    {
        $this->assertTrue($this->validate(
            ['field' => '0470123456', 'field_country' => 'BE'],
            ['field' => (new Phone)->type(PhoneNumberType::MOBILE)]
        )->passes());

        $this->assertFalse($this->validate(
            ['field' => '012345678', 'field_country' => 'BE'],
            ['field' => (new Phone)->type(PhoneNumberType::MOBILE)]
        )->passes());

        $this->assertFalse($this->validate(
            ['field' => '0470123456', 'field_country' => 'NL'],
            ['field' => (new Phone)->type(PhoneNumberType::MOBILE)]
        )->passes());
    }

    #[Test]
    public function it_validates_type_and_custom_country_field_combined()
    {
        $this->assertTrue($this->validate(
            ['field' => '0470123456', 'some_country' => 'BE'],
            ['field' => (new Phone)->type(PhoneNumberType::MOBILE)->countryField('some_country')]
        )->passes());

        $this->assertFalse($this->validate(
            ['field' => '012345678', 'some_country' => 'BE'],
            ['field' => (new Phone)->type(PhoneNumberType::MOBILE)->countryField('some_country')]
        )->passes());

        $this->assertFalse($this->validate(
            ['field' => '0470123456', 'some_country' => 'NL'],
            ['field' => (new Phone)->type(PhoneNumberType::MOBILE)->countryField('some_country')]
        )->passes());
    }

    #[Test]
    public function it_validates_type_as_string()
    {
        $this->assertTrue($this->validate(
            ['field' => '+32470123456'],
            ['field' => (new Phone)->type('mobile')]
        )->passes());

        $this->assertFalse($this->validate(
            ['field' => '+3212345678'],
            ['field' => (new Phone)->type('mobile')]
        )->passes());
    }

    #[Test]
    public function it_validates_type_case_insensitive()
    {
        $this->assertTrue($this->validate(
            ['field' => '+32470123456'],
            ['field' => (new Phone)->type('MoBIle')]
        )->passes());

        $this->assertFalse($this->validate(
            ['field' => '+3212345678'],
            ['field' => (new Phone)->type('MoBIle')]
        )->passes());
    }

    #[Test]
    public function it_validates_blocked_type()
    {
        $this->assertFalse($this->validate(
            ['field' => '+32470123456'],
            ['field' => (new Phone)->notType(PhoneNumberType::MOBILE)]
        )->passes());

        $this->assertTrue($this->validate(
            ['field' => '+3212345678'],
            ['field' => (new Phone)->notType(PhoneNumberType::MOBILE)]
        )->passes());
    }

    #[Test]
    public function it_validates_blocked_type_and_explicit_country_combined()
    {
        $this->assertFalse($this->validate(
            ['field' => '0470123456'],
            ['field' => (new Phone)->notType(PhoneNumberType::MOBILE)->country('BE')]
        )->passes());

        $this->assertTrue($this->validate(
            ['field' => '012345678'],
            ['field' => (new Phone)->notType(PhoneNumberType::MOBILE)->country('BE')]
        )->passes());

        $this->assertFalse($this->validate(
            ['field' => '0470123456'],
            ['field' => (new Phone)->notType(PhoneNumberType::MOBILE)->country('US')]
        )->passes());
    }

    #[Test]
    public function it_validates_blocked_type_and_implicit_country_field_combined()
    {
        $this->assertFalse($this->validate(
            ['field' => '0470123456', 'field_country' => 'BE'],
            ['field' => (new Phone)->notType(PhoneNumberType::MOBILE)]
        )->passes());

        $this->assertTrue($this->validate(
            ['field' => '012345678', 'field_country' => 'BE'],
            ['field' => (new Phone)->notType(PhoneNumberType::MOBILE)]
        )->passes());

        $this->assertFalse($this->validate(
            ['field' => '0470123456', 'field_country' => 'US'],
            ['field' => (new Phone)->notType(PhoneNumberType::MOBILE)]
        )->passes());
    }

    #[Test]
    public function it_validates_blocked_type_and_custom_country_field_combined()
    {
        $this->assertFalse($this->validate(
            ['field' => '0470123456', 'some_country' => 'BE'],
            ['field' => (new Phone)->notType(PhoneNumberType::MOBILE)->countryField('some_country')]
        )->passes());

        $this->assertTrue($this->validate(
            ['field' => '012345678', 'some_country' => 'BE'],
            ['field' => (new Phone)->notType(PhoneNumberType::MOBILE)->countryField('some_country')]
        )->passes());

        $this->assertFalse($this->validate(
            ['field' => '0470123456', 'some_country' => 'US'],
            ['field' => (new Phone)->notType(PhoneNumberType::MOBILE)->countryField('some_country')]
        )->passes());
    }

    #[Test]
    public function it_validates_blocked_type_as_string()
    {
        $this->assertFalse($this->validate(
            ['field' => '+32470123456'],
            ['field' => (new Phone)->notType('mobile')]
        )->passes());

        $this->assertTrue($this->validate(
            ['field' => '+3212345678'],
            ['field' => (new Phone)->notType('mobile')]
        )->passes());
    }

    #[Test]
    public function it_validates_blocked_type_case_insensitive()
    {
        $this->assertFalse($this->validate(
            ['field' => '+32470123456'],
            ['field' => (new Phone)->notType('MoBIle')]
        )->passes());

        $this->assertTrue($this->validate(
            ['field' => '+3212345678'],
            ['field' => (new Phone)->notType('MoBIle')]
        )->passes());
    }

    #[Test]
    public function it_doesnt_allow_allowed_and_blocked_types_simultaneously()
    {
        $this->expectException(IncompatibleTypesException::class);

        $this->validate(
            ['field' => '+3212345678'],
            ['field' => (new Phone)->type('fixed_line')->notType('mobile')]
        )->passes();
    }

    #[Test]
    public function it_prevents_parameter_hijacking_through_the_country_field()
    {
        $this->assertFalse($this->validate(
            ['field' => '0470123456', 'field_country' => 'mobile'],
            ['field' => (new Phone)->type('fixed_line')->country('BE')]
        )->passes());
    }

    #[Test]
    public function it_copes_with_nullable_validation_rule()
    {
        $this->assertTrue($this->validate(
            ['field' => null],
            ['field' => ['nullable', (new Phone)->country('BE')]]
        )->passes());
    }

    #[Test]
    public function it_copes_with_required_if_validation_rule()
    {
        $this->assertTrue($this->validate(
            ['other' => 0],
            ['field' => ['required_if:other,1', (new Phone)->country('BE')]]
        )->passes());
    }

    #[Test]
    public function it_validates_libphonenumber_specific_regions_as_country()
    {
        $this->assertTrue($this->validate(
            ['field' => '+247501234'],
            ['field' => [(new Phone)->country('AC')]]
        )->passes());
    }

    #[Test]
    public function it_resolves_validator_alias()
    {
        $this->assertTrue($this->validate(
            ['field' => '0470123456'],
            ['field' => ['phone:be,mobile']]
        )->passes());

        $this->assertFalse($this->validate(
            ['field' => '012345678'],
            ['field' => ['phone:nl']]
        )->passes());
    }

    #[Test]
    public function it_resolves_rule_macro()
    {
        $this->assertEquals(new Phone, Rule::phone());
    }

    #[Test]
    public function it_translates_validation_message()
    {
        app('translator')->setLocale('xx');

        app('translator')->setLoaded([
            '*' => [
                'validation' => [
                    'xx' => [
                        'phone' => 'foo',
                    ],
                ],
            ],
        ]);

        $message = $this->validate(['field' => '003212345678'], ['field' => new Phone])->errors()->first('field');
        $this->assertEquals('foo', $message);

        $message = $this->validate(['field' => '003212345678'], ['field' => 'phone'])->errors()->first('field');
        $this->assertEquals('foo', $message);

        $message = $this->validate(['field' => '003212345678'], ['field' => Rule::phone()])->errors()->first('field');
        $this->assertEquals('foo', $message);
    }
}
