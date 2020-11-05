<?php

namespace Propaganistas\LaravelPhone\Tests;

use Illuminate\Database\Schema\Blueprint;
use Propaganistas\LaravelPhone\PhoneNumber;
use Propaganistas\LaravelPhone\Tests\Models\Inquiry;
use Propaganistas\LaravelPhone\Tests\Models\User;

class PhoneNumberCastTest extends TestCase
{

    protected function setUp(): void
    {
        parent::setUp();

        $this->createUsersTable();
        $this->createInquiriesTable();
    }

    /** @test */
    public function it_can_cast_phone_number()
    {
        $model = Inquiry::create([
            'mobile_number' => '09123456789',
            'mobile_number_country' => 'PH',
        ]);

        $this->assertEquals('09123456789', $model->mobile_number->getRawNumber());
        $this->assertEquals('PH', $model->mobile_number->getCountry());
    }

    /** @test */
    public function it_can_cast_multiple_phone_number()
    {
        $model = User::create([
            'contact_number' => '09123456789',
            'contact_number_country' => 'PH',
            'emergency_number' => '012345678',
            'emergency_number_country' => 'BE',
        ]);

        $this->assertEquals('09123456789', $model->contact_number->getRawNumber());
        $this->assertEquals('PH', $model->contact_number->getCountry());

        $this->assertEquals('012345678', $model->emergency_number->getRawNumber());
        $this->assertEquals('BE', $model->emergency_number->getCountry());
    }

    private function createUsersTable()
    {
        $this->app['db']->connection()->getSchemaBuilder()->create('users', function (Blueprint $table) {
            $table->increments('id');
            $table->string('contact_number');
            $table->char('contact_number_country', 2);
            $table->string('emergency_number');
            $table->char('emergency_number_country', 2);
        });
    }

    private function createInquiriesTable()
    {
        $this->app['db']->connection()->getSchemaBuilder()->create('inquiries', function (Blueprint $table) {
            $table->increments('id');
            $table->string('mobile_number');
            $table->char('mobile_number_country', 2);
        });
    }
}
