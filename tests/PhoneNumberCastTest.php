<?php

namespace Propaganistas\LaravelPhone\Tests;

use Illuminate\Database\Schema\Blueprint;
use Propaganistas\LaravelPhone\Models\PhoneNumberCast;
use Propaganistas\LaravelPhone\PhoneNumber;
use Propaganistas\LaravelPhone\Tests\Models\User;

class PhoneNumberCastTest extends TestCase
{

    protected function setUp(): void
    {
        parent::setUp();

        $this->createUsersTable();
    }

    /** @test */
    public function it_can_cast()
    {
        $model = User::create([
            'contact_number' => '09123456789',
            'contact_number_country' => 'PH',
        ]);

        $this->assertEquals('+639123456789', (string) $model->contact_number);
        $this->assertEquals('PH', $model->contact_number->getCountry());
    }

    /** @test */
    public function it_can_cast_with_PhoneNumber_class()
    {
        $modelClass = new class () extends User
        {
            protected $casts = ['contact_number' => PhoneNumber::class . ':contact_number_country'];
        };

        $model = $modelClass::create([
            'contact_number' => '09123456789',
            'contact_number_country' => 'PH',
        ]);

        $this->assertEquals('+639123456789', (string) $model->contact_number);
        $this->assertEquals('PH', $model->contact_number->getCountry());
    }

    /** @test */
    public function it_can_cast_with_default_country()
    {
        $modelClass = new class () extends User
        {
            protected $casts = ['contact_number' => PhoneNumberCast::class . ':PH'];
        };

        $model = $modelClass::create(['contact_number' => '09123456789']);

        $this->assertEquals('+639123456789', (string) $model->contact_number);
        $this->assertEquals('PH', $model->contact_number->getCountry());
    }

    /** @test */
    public function it_can_cast_with_default_country_and_target_column()
    {
        $modelClass = new class () extends User
        {
            protected $casts = ['contact_number' => PhoneNumberCast::class . ':PH,contact_number_country'];
        };

        $modelUsesDefaultCountry = $modelClass::create(['contact_number' => '09123456789']);

        $this->assertEquals('+639123456789', (string) $modelUsesDefaultCountry->contact_number);
        $this->assertEquals('PH', $modelUsesDefaultCountry->contact_number->getCountry());

        $modelUsesCountryColumn = $modelClass::create([
            'contact_number' => '012345678',
            'contact_number_country' => 'BE',
        ]);

        $this->assertEquals('+3212345678', (string) $modelUsesCountryColumn->contact_number);
        $this->assertEquals('BE', $modelUsesCountryColumn->contact_number->getCountry());
    }

    private function createUsersTable()
    {
        $this->app['db']->connection()->getSchemaBuilder()->create('users', function (Blueprint $table) {
            $table->increments('id');
            $table->string('contact_number');
            $table->char('contact_number_country', 2)->nullable();
        });
    }
}
