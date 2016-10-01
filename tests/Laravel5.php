<?php namespace Propaganistas\LaravelPhone\Tests;

use Propaganistas\LaravelPhone\LaravelPhoneFacade;

class Laravel5 extends PhoneValidatorTest
{

    /**
     * @param \Illuminate\Foundation\Application $app
     * @return array
     */
	protected function getPackageProviders($app)
	{
		return ['Propaganistas\LaravelPhone\LaravelPhoneServiceProvider'];
	}

    /**
     * @param \Illuminate\Foundation\Application $app
     * @return array
     */
    protected function getPackageAliases($app)
    {
        return [
            'Phone' => LaravelPhoneFacade::class,
        ];
    }
}
