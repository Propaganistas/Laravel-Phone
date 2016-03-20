<?php namespace Propaganistas\LaravelPhone\Tests;

class Laravel4 extends PhoneValidatorTest
{

	protected function getPackageProviders()
	{
		return ['Propaganistas\LaravelPhone\LaravelPhoneServiceProvider'];
	}
}
