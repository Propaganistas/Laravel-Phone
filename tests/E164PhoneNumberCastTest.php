<?php

namespace Propaganistas\LaravelPhone\Tests;

use Illuminate\Database\Eloquent\Model;
use Propaganistas\LaravelPhone\Casts\E164PhoneNumberCast;
use Propaganistas\LaravelPhone\PhoneNumber;
use UnexpectedValueException;

class E164PhoneNumberCastTest extends TestCase
{
    /** @test */
    public function it_mutates_to_e164_number()
    {
        $model = new ModelWithE164Cast;
        $model->phone = '+32 12 34 56 78';
        $this->assertEquals('+3212345678', $model->getAttributes()['phone']);

        $model = new ModelWithE164Cast;
        $model->phone = PhoneNumber::make('+32 12/34.56.78');
        $this->assertEquals('+3212345678', $model->getAttributes()['phone']);
    }

    /** @test */
    public function it_mutates_to_e164_number_with_implicit_country_field()
    {
        $model = new ModelWithE164Cast;
        $model->phone_country = 'BE';
        $model->phone = '012 34 56 78';
        $this->assertEquals('+3212345678', $model->getAttributes()['phone']);

        $model = new ModelWithE164Cast;
        $model->phone_country = 'BE';
        $model->phone = PhoneNumber::make('+32 12/34.56.78');
        $this->assertEquals('+3212345678', $model->getAttributes()['phone']);
    }

    /** @test */
    public function it_mutates_to_e164_number_with_explicit_country_field()
    {
        $model = new ModelWithE164CastAndCountryField;
        $model->country = 'BE';
        $model->phone = '012 34 56 78';
        $this->assertEquals('+3212345678', $model->getAttributes()['phone']);

        $model = new ModelWithE164CastAndCountryField;
        $model->country = 'BE';
        $model->phone = PhoneNumber::make('+32 12/34.56.78');
        $this->assertEquals('+3212345678', $model->getAttributes()['phone']);
    }

    /** @test */
    public function it_gets_phone_object()
    {
        $model = new ModelWithE164Cast;
        $model->setRawAttributes(['phone' => '+3212345678']);
        $this->assertIsObject($model->phone);
        $this->assertEquals(PhoneNumber::class, get_class($model->phone));
    }

    /** @test */
    public function it_throws_when_accessing_non_international_value()
    {
        $model = new ModelWithE164Cast();
        $model->setRawAttributes(['phone' => '012 34 56 78']);
        $this->expectException(UnexpectedValueException::class);
        $model->phone;
    }
}

class ModelWithE164Cast extends Model
{
    protected $casts = [
        'phone' => E164PhoneNumberCast::class,
    ];
}

class ModelWithE164CastAndCountryField extends Model
{
    protected $casts = [
        'phone' => E164PhoneNumberCast::class.':country',
    ];
}