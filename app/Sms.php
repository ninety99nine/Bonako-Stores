<?php

namespace App;

use App\Traits\SmsTraits;
use App\Traits\CommonTraits;
use Illuminate\Database\Eloquent\Model;

class Sms extends Model
{
    use SmsTraits;
    use CommonTraits;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [

        'message', 'mobile_number', 'type', 'status', 'error_message', 'origin_id', 'origin_type'

    ];

    /** ATTRIBUTES
     *
     *  Note that the "resource_type" is defined within CommonTraits.
     */
    protected $appends = [
        'resource_type',
    ];

}
