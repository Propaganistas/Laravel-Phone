<?php

namespace Propaganistas\LaravelPhone\Models;

interface HasPhoneNumber
{
    /**
     * Get the phone number country column for a certain field
     * 
     * @param string $field
     * 
     * @return string
     */
    public function getPhoneNumberCountryColumn($field);
}
