<?php

namespace Propaganistas\LaravelPhone\Tests;

use Illuminate\Database\Eloquent\Model;
use InvalidArgumentException;
use PHPUnit\Framework\Attributes\Test;
use Propaganistas\LaravelPhone\Casts\RawPhoneNumberCast;
use Propaganistas\LaravelPhone\PhoneNumber;

class RawPhoneNumberCastTest extends TestCase
{
    #[Test]
    public function it_mutates_to_raw_number()
    {
        $model = new ModelWithRawCast;
        $model->phone = '012 34 56 78';

        $model->phone;
        $this->assertEquals('012 34 56 78', $model->getAttributes()['phone']);

        $model = new ModelWithRawCast;
        $model->phone = new PhoneNumber('012/34.56.78');
        $this->assertEquals('012/34.56.78', $model->getAttributes()['phone']);

        $model = new ModelWithRawCast;
        $model->phone = new PhoneNumber('012345678', 'BE');
        $this->assertEquals('012345678', $model->getAttributes()['phone']);

        $model = new ModelWithRawCast;
        $model->phone = new PhoneNumber('012-34-56-78', 'US');
        $this->assertEquals('012-34-56-78', $model->getAttributes()['phone']);
    }

    #[Test]
    public function it_gets_phone_object()
    {
        $model = new ModelWithRawCast;
        $model->setRawAttributes(['phone' => '012 34 56 78']);
        $this->assertIsObject($model->phone);
        $this->assertEquals(PhoneNumber::class, get_class($model->phone));
    }

    #[Test]
    public function it_gets_with_implicit_country_field()
    {
        $model = new ModelWithIncompleteRawCast;
        $model->setRawAttributes([
            'phone_country' => 'BE',
            'phone' => '012 34 56 78',
        ]);
        $this->assertIsObject($model->phone);
        $this->assertEquals(PhoneNumber::class, get_class($model->phone));
    }

    #[Test]
    public function it_gets_with_explicit_country_field()
    {
        $model = new ModelWithRawCastAndCountryField;
        $model->setRawAttributes([
            'country' => 'BE',
            'phone' => '012 34 56 78',
        ]);
        $this->assertIsObject($model->phone);
        $this->assertEquals(PhoneNumber::class, get_class($model->phone));
    }

    #[Test]
    public function it_throws_when_accessing_incomplete_raw_cast()
    {
        $model = new ModelWithIncompleteRawCast;
        $model->setRawAttributes(['phone' => '012 34 56 78']);
        $this->expectException(InvalidArgumentException::class);
        $model->phone;
    }

    #[Test]
    public function it_gets_phone_object_when_accessing_incomplete_raw_cast_with_international_number()
    {
        $model = new ModelWithIncompleteRawCast;
        $model->setRawAttributes(['phone' => '+32 12 34 56 78']);

        $this->assertIsObject($model->phone);
        $this->assertEquals(PhoneNumber::class, get_class($model->phone));
    }

    #[Test]
    public function it_serializes()
    {
        $model = new ModelWithRawCast;
        $model->phone = '012 34 56 78';
        $this->assertEquals('012 34 56 78', $model->toArray()['phone']);

        $model = new ModelWithRawCast;
        $model->phone = null;
        $this->assertEquals(null, $model->toArray()['phone']);
    }
}

class ModelWithRawCast extends Model
{
    protected $casts = [
        'phone' => RawPhoneNumberCast::class.':BE,NL',
    ];
}

class ModelWithRawCastAndCountryField extends Model
{
    protected $casts = [
        'phone' => RawPhoneNumberCast::class.':country',
    ];
}

class ModelWithIncompleteRawCast extends Model
{
    protected $casts = [
        'phone' => RawPhoneNumberCast::class,
    ];
}
