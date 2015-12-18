<?php namespace Propaganistas\LaravelPhone\Tests;

class Laravel5 extends PhoneValidatorTest
{

    protected function getPackageProviders($app)
    {
        return ['Propaganistas\LaravelPhone\LaravelPhoneServiceProvider'];
    }
}
