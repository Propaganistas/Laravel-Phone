<?php

namespace Propaganistas\LaravelPhone\Tests;

use libphonenumber\PhoneNumber;

class FacadeTest extends Laravel5
{
    public function testParseMethod()
    {
        $phoneNumber = \Phone::parse('650-429-2057', 'US');
        $this->assertTrue($phoneNumber instanceof PhoneNumber);
    }
}