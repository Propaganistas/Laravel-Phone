<?php

namespace Propaganistas\LaravelPhone\Tests;

use Illuminate\Database\Schema\Blueprint;
use Propaganistas\LaravelPhone\Models\PhoneNumberCast;
use Propaganistas\LaravelPhone\PhoneNumber;
use Propaganistas\LaravelPhone\Tests\Models\User;

class PhoneNumberCastTest extends TestCase
{
    /** @test */
    public function it_can_cast()
    {
        $model = new User;
        $model->contact_number = '09123456789';
        $model->contact_number_country = 'PH';

        $this->assertEquals('+639123456789', (string) $model->contact_number);
        $this->assertEquals('PH', $model->contact_number->getCountry());
        $this->assertEquals('09123456789', $model->getAttributes()['contact_number']);
    }

    /** @test */
    public function it_can_cast_with_PhoneNumber_class()
    {
        $model = new class () extends User
        {
            protected $casts = ['contact_number' => PhoneNumber::class . ':contact_number_country'];
        };

        $model->contact_number = '09123456789';
        $model->contact_number_country = 'PH';

        $this->assertEquals('+639123456789', (string) $model->contact_number);
        $this->assertEquals('PH', $model->contact_number->getCountry());
        $this->assertEquals('09123456789', $model->getAttributes()['contact_number']);
    }

    /** @test */
    public function it_can_cast_with_default_country()
    {
        $model = new class () extends User
        {
            protected $casts = ['contact_number' => PhoneNumberCast::class . ':PH'];
        };
        $model->contact_number = '09123456789';

        $this->assertEquals('+639123456789', (string) $model->contact_number);
        $this->assertEquals('PH', $model->contact_number->getCountry());
        $this->assertEquals('09123456789', $model->getAttributes()['contact_number']);
    }

    /** @test */
    public function it_can_cast_with_default_country_and_target_column()
    {
        $model = new class () extends User
        {
            protected $casts = ['contact_number' => PhoneNumberCast::class . ':PH,contact_number_country'];
        };

        $modelUsesDefaultCountry = $model->replicate();
        $modelUsesDefaultCountry->contact_number = '09123456789';

        $this->assertEquals('+639123456789', (string) $modelUsesDefaultCountry->contact_number);
        $this->assertEquals('PH', $modelUsesDefaultCountry->contact_number->getCountry());
        $this->assertEquals('09123456789', $modelUsesDefaultCountry->getAttributes()['contact_number']);

        $modelUsesCountryColumn = $model->replicate();
        $modelUsesCountryColumn->contact_number = '012345678';
        $modelUsesCountryColumn->contact_number_country = 'BE';

        $this->assertEquals('+3212345678', (string) $modelUsesCountryColumn->contact_number);
        $this->assertEquals('BE', $modelUsesCountryColumn->contact_number->getCountry());
        $this->assertEquals('012345678', $modelUsesCountryColumn->getAttributes()['contact_number']);
    }
}
