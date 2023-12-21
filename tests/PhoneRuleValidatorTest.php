<?php

namespace Propaganistas\LaravelPhone\Tests;

use Illuminate\Validation\Validator;
use libphonenumber\PhoneNumberType;
use Propaganistas\LaravelPhone\Rules\Phone;
use Propaganistas\LaravelPhone\Exceptions\IncompatibleTypesException;

class PhoneRuleValidatorTest extends TestCase
{
    protected function validate(array $data, array $rules): Validator
    {
        return $this->app['validator']->make($data, $rules);
    }

    /** @test */
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

    /** @test */
    public function it_validates_negative_type()
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

    /** @test */
    public function it_doesnt_allow_type_and_not_type()
    {
        $this->expectException(IncompatibleTypesException::class);

        $this->validate(
            ['field' => '+3212345678'],
            ['field' => (new Phone)->type(PhoneNumberType::MOBILE)->notType(PhoneNumberType::MOBILE)]
        )->passes();
    }
}