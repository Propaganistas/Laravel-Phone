<?php

namespace Propaganistas\LaravelPhone;

/**
 * Class LaravelPhoneFacade
 * @package Propaganistas\LaravelPhone
 */
class LaravelPhoneFacade extends \Illuminate\Support\Facades\Facade
{
    /**
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        return 'libphonenumber';
    }
}
