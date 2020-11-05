<?php

namespace Propaganistas\LaravelPhone\Tests\Models;

use Illuminate\Database\Eloquent\Model;
use Propaganistas\LaravelPhone\Models\PhoneNumberCast;

class Inquiry extends Model
{
    protected $fillable = [
        'mobile_number',
        'mobile_number_country',
    ];

    protected $casts = [
        'mobile_number' => PhoneNumberCast::class . ':mobile_number_country'
    ];

    public $timestamps = false;
}
