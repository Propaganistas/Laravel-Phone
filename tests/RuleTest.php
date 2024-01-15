<?php

namespace Propaganistas\LaravelPhone\Tests;

use Illuminate\Validation\Validator;
use libphonenumber\PhoneNumberType;
use Propaganistas\LaravelPhone\Exceptions\IncompatibleTypesException;
use Propaganistas\LaravelPhone\Rules\Phone;
use ReflectionClass;

class RuleTest extends TestCase
{
    /** @test */
    public function it_defaults_to_nothing()
    {
        $rule = (new Phone);

        $this->assertEquals([], $this->getProtectedProperty($rule, 'countries'));
        $this->assertNull($this->getProtectedProperty($rule, 'countryField'));
        $this->assertEquals([], $this->getProtectedProperty($rule, 'allowedTypes'));
        $this->assertEquals([], $this->getProtectedProperty($rule, 'blockedTypes'));
        $this->assertFalse($this->getProtectedProperty($rule, 'lenient'));
        $this->assertFalse($this->getProtectedProperty($rule, 'international'));
    }

    /** @test */
    public function it_sets_countries()
    {
        $rule = (new Phone)->country('BE');
        $this->assertEquals(['BE'], $this->getProtectedProperty($rule, 'countries'));

        $rule = (new Phone)->country(['BE', 'NL']);
        $this->assertEquals(['BE', 'NL'], $this->getProtectedProperty($rule, 'countries'));
    }

    /** @test */
    public function it_merges_existing_countries()
    {
        $rule = (new Phone)->country('BE');
        $rule->country('NL');

        $this->assertEquals(['BE', 'NL'], $this->getProtectedProperty($rule, 'countries'));
    }

    /** @test */
    public function it_sets_countryField()
    {
        $rule = (new Phone)->countryField('foo');
        $this->assertEquals('foo', $this->getProtectedProperty($rule, 'countryField'));
    }

    /** @test */
    public function it_sets_types()
    {
        $rule = (new Phone)->type('mobile');
        $this->assertEquals(['mobile'], $this->getProtectedProperty($rule, 'allowedTypes'));

        $rule = (new Phone)->type(['mobile', 'fixed_line']);
        $this->assertEquals(['mobile', 'fixed_line'], $this->getProtectedProperty($rule, 'allowedTypes'));
    }

    /** @test */
    public function it_merges_existing_types()
    {
        $rule = (new Phone)->type('mobile');
        $rule->type('fixed_line');

        $this->assertEquals(['mobile', 'fixed_line'], $this->getProtectedProperty($rule, 'allowedTypes'));
    }

    /** @test */
    public function it_sets_blocked_types()
    {
        $rule = (new Phone)->notType('mobile');
        $this->assertEquals(['mobile'], $this->getProtectedProperty($rule, 'blockedTypes'));

        $rule = (new Phone)->notType(['mobile', 'fixed_line']);
        $this->assertEquals(['mobile', 'fixed_line'], $this->getProtectedProperty($rule, 'blockedTypes'));
    }

    /** @test */
    public function it_merges_existing_blocked_types()
    {
        $rule = (new Phone)->notType('mobile');
        $rule->notType('fixed_line');

        $this->assertEquals(['mobile', 'fixed_line'], $this->getProtectedProperty($rule, 'blockedTypes'));
    }

    /** @test */
    public function it_sets_mobile_type_using_shortcut_method()
    {
        $rule = (new Phone)->mobile();
        $this->assertEquals([PhoneNumberType::MOBILE], $this->getProtectedProperty($rule, 'allowedTypes'));
    }

    /** @test */
    public function it_sets_fixed_line_type_using_shortcut_method()
    {
        $rule = (new Phone)->fixedLine();
        $this->assertEquals([PhoneNumberType::FIXED_LINE], $this->getProtectedProperty($rule, 'allowedTypes'));
    }

    /** @test */
    public function it_sets_lenient_mode()
    {
        $rule = (new Phone)->lenient();
        $this->assertTrue($this->getProtectedProperty($rule, 'lenient'));
    }

    /** @test */
    public function it_sets_international_mode()
    {
        $rule = (new Phone)->international();
        $this->assertTrue($this->getProtectedProperty($rule, 'international'));
    }

    /** @test */
    public function it_returns_default_validation_message()
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

        $this->assertEquals('foo', (new Phone)->message());
    }

    /** @test */
    public function it_converts_string_validation_parameters()
    {
        $base = (new Phone)->setValidator(validator(['foo' => null]));

        $rule = (clone $base)->setParameters('lenient');
        $this->assertTrue($this->getProtectedProperty($rule, 'lenient'));

        $rule = (clone $base)->setParameters('international');
        $this->assertTrue($this->getProtectedProperty($rule, 'international'));

        $rule = (clone $base)->setParameters('foo');
        $this->assertEquals('foo', $this->getProtectedProperty($rule, 'countryField'));

        $rule = (clone $base)->setParameters('be');
        $this->assertEquals(['be'], $this->getProtectedProperty($rule, 'countries'));

        $rule = (clone $base)->setParameters('mobile');
        $this->assertEquals(['mobile'], $this->getProtectedProperty($rule, 'allowedTypes'));

        $rule = (clone $base)->setParameters('fixed_line');
        $this->assertEquals(['fixed_line'], $this->getProtectedProperty($rule, 'allowedTypes'));

        $rule = (clone $base)->setParameters([(string) PhoneNumberType::MOBILE]);
        $this->assertEquals([PhoneNumberType::MOBILE], $this->getProtectedProperty($rule, 'allowedTypes'));

        $rule = (clone $base)->setParameters(['!mobile']);
        $this->assertEquals(['mobile'], $this->getProtectedProperty($rule, 'blockedTypes'));

        $rule = (clone $base)->setParameters(['!fixed_line']);
        $this->assertEquals(['fixed_line'], $this->getProtectedProperty($rule, 'blockedTypes'));

        $rule = (clone $base)->setParameters(['!'.PhoneNumberType::MOBILE]);
        $this->assertEquals([PhoneNumberType::MOBILE], $this->getProtectedProperty($rule, 'blockedTypes'));

        $rule = (clone $base)->setParameters(['lenient', 'international', 'foo', 'be', 'nl', 'mobile', 'fixed_line']);
        $this->assertTrue($this->getProtectedProperty($rule, 'lenient'));
        $this->assertTrue($this->getProtectedProperty($rule, 'international'));
        $this->assertEquals('foo', $this->getProtectedProperty($rule, 'countryField'));
        $this->assertEquals(['be','nl'], $this->getProtectedProperty($rule, 'countries'));
        $this->assertEquals(['mobile', 'fixed_line'], $this->getProtectedProperty($rule, 'allowedTypes'));

        $rule = (clone $base)->setParameters(['lenient', 'international', 'foo', 'be', 'nl', '!mobile', '!fixed_line']);
        $this->assertTrue($this->getProtectedProperty($rule, 'lenient'));
        $this->assertTrue($this->getProtectedProperty($rule, 'international'));
        $this->assertEquals('foo', $this->getProtectedProperty($rule, 'countryField'));
        $this->assertEquals(['be','nl'], $this->getProtectedProperty($rule, 'countries'));
        $this->assertEquals(['mobile', 'fixed_line'], $this->getProtectedProperty($rule, 'blockedTypes'));
    }

    /** @test */
    public function it_allows_data_field_names_to_be_parameters()
    {
        $base = (new Phone)->setValidator(validator([
            'mobile' => null,
            'fixed_line' => null,
            'lenient' => null,
            'international' => null,
        ]));

        $rule = $base->setParameters(['mobile', 'fixed_line', 'lenient', 'international']);
        $this->assertEquals(['mobile', 'fixed_line'], $this->getProtectedProperty($rule, 'allowedTypes'));
        $this->assertTrue($this->getProtectedProperty($rule, 'lenient'));
        $this->assertTrue($this->getProtectedProperty($rule, 'international'));
    }

    /** @test */
    public function it_treats_string_validation_parameters_case_insensitive()
    {
        $base = (new Phone)->setValidator(validator(['foo' => null]));

        $rule = (clone $base)->setParameters('LeNIent');
        $this->assertTrue($this->getProtectedProperty($rule, 'lenient'));

        $rule = (clone $base)->setParameters('InteRNAtional');
        $this->assertTrue($this->getProtectedProperty($rule, 'international'));

        $rule = (clone $base)->setParameters('MoBIle');
        $this->assertEquals(['MoBIle'], $this->getProtectedProperty($rule, 'allowedTypes'));

        $rule = (clone $base)->setParameters(['!MoBIle']);
        $this->assertEquals(['MoBIle'], $this->getProtectedProperty($rule, 'blockedTypes'));
    }

    /** @test */
    public function it_ignores_invalid_string_validation_parameters()
    {
        $base = (new Phone)->setValidator(validator([]));
        $rule = (clone $base)->setParameters(['xyz', 'foo']);

        $this->assertEquals([], $this->getProtectedProperty($rule, 'countries'));
        $this->assertNull($this->getProtectedProperty($rule, 'countryField'));
        $this->assertEquals([], $this->getProtectedProperty($rule, 'allowedTypes'));
        $this->assertEquals([], $this->getProtectedProperty($rule, 'blockedTypes'));
        $this->assertFalse($this->getProtectedProperty($rule, 'lenient'));
        $this->assertFalse($this->getProtectedProperty($rule, 'international'));
    }

    protected function getProtectedProperty(object $object, string $property)
    {
        $property = (new ReflectionClass($object))->getProperty($property);
        $property->setAccessible(true);

        return $property->getValue($object);
    }
}
