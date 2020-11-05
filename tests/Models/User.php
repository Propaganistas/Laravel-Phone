<?php

namespace Propaganistas\LaravelPhone\Tests\Models;

use Illuminate\Database\Eloquent\Model;
use Propaganistas\LaravelPhone\Models\PhoneNumberCast;

class User extends Model
{
    protected $fillable = [
        'contact_number',
        'contact_number_country',
        'emergency_number',
        'emergency_number_country',
    ];

    protected $casts = [
        'contact_number' => PhoneNumberCast::class . ':contact_number_country',
        'emergency_number' => PhoneNumberCast::class . ':emergency_number_country',
    ];

    public $timestamps = false;
}
