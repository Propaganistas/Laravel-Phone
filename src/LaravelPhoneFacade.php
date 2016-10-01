<?php namespace Propaganistas\LaravelPhone;

use Illuminate\Support\Facades\Facade;

class LaravelPhoneFacade extends Facade
{
    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        return 'libphonenumber';
    }
}
