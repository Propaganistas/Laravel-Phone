<?php

namespace Propaganistas\LaravelPhone\Tests\Models;

use Illuminate\Database\Eloquent\Model;
use Propaganistas\LaravelPhone\Models\HasPhoneNumber;
use Propaganistas\LaravelPhone\Models\PhoneNumberCast;

class Inquiry extends Model implements HasPhoneNumber
{
    protected $table = 'inquiries';

    protected $fillable = [
        'mobile_number',
        'mobile_number_country',
    ];

    protected $casts = [
        'mobile_number' => PhoneNumberCast::class
    ];

    public $timestamps = false;

    public function getPhoneNumberCountryColumn($field)
    {
        return 'mobile_number_country';
    }
}
