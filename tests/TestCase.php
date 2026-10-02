<?php

namespace Propaganistas\LaravelPhone\Tests;

use Orchestra\Testbench\TestCase as BaseTestCase;
use Propaganistas\LaravelPhone\PhoneServiceProvider;
use Propaganistas\LaravelPhone\Rules\Phone;

abstract class TestCase extends BaseTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        Phone::setDefaultCountry([]);
    }

    /**
     * @param  \Illuminate\Foundation\Application  $application
     * @return array
     */
    protected function getPackageProviders($application)
    {
        return [PhoneServiceProvider::class];
    }
}
