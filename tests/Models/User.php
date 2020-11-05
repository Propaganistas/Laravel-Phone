<?php

namespace Propaganistas\LaravelPhone\Tests\Models;

use Illuminate\Database\Eloquent\Model;
use Propaganistas\LaravelPhone\Models\HasPhoneNumber;
use Propaganistas\LaravelPhone\Models\PhoneNumberCast;

class User extends Model implements HasPhoneNumber
{
    protected $table = 'users';

    protected $fillable = [
        'contact_number',
        'contact_number_country',
        'emergency_number',
        'emergency_number_country',
    ];

    protected $casts = [
        'contact_number' => PhoneNumberCast::class,
        'emergency_number' => PhoneNumberCast::class
    ];

    public $timestamps = false;

    public function getPhoneNumberCountryColumn($field)
    {
        switch ($field) {
            case 'contact_number':
                return 'contact_number_country';
            case 'emergency_number':
                return 'emergency_number_country';
        }
    }
}
