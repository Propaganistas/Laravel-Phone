<?php

namespace Propaganistas\LaravelPhone\Tests;

use Illuminate\Database\Eloquent\Model;
use PHPUnit\Framework\Attributes\Test;
use Propaganistas\LaravelPhone\Casts\E164PhoneNumberCast;
use Propaganistas\LaravelPhone\PhoneNumber;
use UnexpectedValueException;

class E164PhoneNumberCastTest extends TestCase
{
    #[Test]
    public function it_supports_no_parameters()
    {
        $model = new ModelWithE164Cast;
        $model->phone = '+32 12 34 56 78';
        $this->assertEquals('+3212345678', $model->getAttributes()['phone']);
        $this->assertInstanceOf(PhoneNumber::class, $model->phone);
        $this->assertEquals('012 34 56 78', $model->phone->formatNational());
        $this->assertEquals('BE', $model->phone->getCountry());

        $model = new ModelWithE164Cast;
        $model->phone = new PhoneNumber('+32 12/34.56.78');
        $this->assertEquals('+3212345678', $model->getAttributes()['phone']);
        $this->assertInstanceOf(PhoneNumber::class, $model->phone);
        $this->assertEquals('012 34 56 78', $model->phone->formatNational());
        $this->assertEquals('BE', $model->phone->getCountry());
    }

    #[Test]
    public function it_supports_implicit_country_field()
    {
        $model = new ModelWithE164Cast;
        $model->phone_country = 'BE';
        $model->phone = '012 34 56 78';
        $this->assertEquals('+3212345678', $model->getAttributes()['phone']);
        $this->assertInstanceOf(PhoneNumber::class, $model->phone);
        $this->assertEquals('012 34 56 78', $model->phone->formatNational());
        $this->assertEquals('BE', $model->phone->getCountry());

        $model = new ModelWithE164Cast;
        $model->phone_country = 'BE';
        $model->phone = new PhoneNumber('+32 12/34.56.78');
        $this->assertEquals('+3212345678', $model->getAttributes()['phone']);
        $this->assertInstanceOf(PhoneNumber::class, $model->phone);
        $this->assertEquals('012 34 56 78', $model->phone->formatNational());
        $this->assertEquals('BE', $model->phone->getCountry());
    }

    #[Test]
    public function it_supports_explicit_country_field()
    {
        $model = new ModelWithE164CastAndCountryField;
        $model->country = 'BE';
        $model->phone = '012 34 56 78';
        $this->assertEquals('+3212345678', $model->getAttributes()['phone']);
        $this->assertInstanceOf(PhoneNumber::class, $model->phone);
        $this->assertEquals('012 34 56 78', $model->phone->formatNational());
        $this->assertEquals('BE', $model->phone->getCountry());

        $model = new ModelWithE164CastAndCountryField;
        $model->country = 'BE';
        $model->phone = new PhoneNumber('+32 12/34.56.78');
        $this->assertEquals('+3212345678', $model->getAttributes()['phone']);
        $this->assertInstanceOf(PhoneNumber::class, $model->phone);
        $this->assertEquals('012 34 56 78', $model->phone->formatNational());
        $this->assertEquals('BE', $model->phone->getCountry());
    }

    #[Test]
    public function it_supports_explicit_country()
    {
        $model = new ModelWithE164CastAndCountry;
        $model->phone = '012 34 56 78';
        $this->assertEquals('+3212345678', $model->getAttributes()['phone']);
        $this->assertInstanceOf(PhoneNumber::class, $model->phone);
        $this->assertEquals('012 34 56 78', $model->phone->formatNational());
        $this->assertEquals('BE', $model->phone->getCountry());

        $model = new ModelWithE164CastAndCountry;
        $model->phone = new PhoneNumber('+32 12/34.56.78');
        $this->assertEquals('+3212345678', $model->getAttributes()['phone']);
        $this->assertInstanceOf(PhoneNumber::class, $model->phone);
        $this->assertEquals('012 34 56 78', $model->phone->formatNational());
        $this->assertEquals('BE', $model->phone->getCountry());
    }

    #[Test]
    public function it_throws_when_accessing_countryless_value()
    {
        $model = new ModelWithE164Cast;
        $model->setRawAttributes(['phone' => '012 34 56 78']);
        $this->expectException(UnexpectedValueException::class);
        $model->phone;
    }

    #[Test]
    public function it_serializes()
    {
        $model = new ModelWithE164Cast;
        $model->phone = '+32 12 34 56 78';
        $this->assertEquals('+3212345678', $model->toArray()['phone']);

        $model = new ModelWithE164Cast;
        $model->phone = null;
        $this->assertEquals(null, $model->toArray()['phone']);
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

class ModelWithE164CastAndCountry extends Model
{
    protected $casts = [
        'phone' => E164PhoneNumberCast::class.':BE',
    ];
}
