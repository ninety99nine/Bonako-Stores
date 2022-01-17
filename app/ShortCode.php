<?php

namespace App;

use App\Traits\CommonTraits;
use App\Traits\ShortCodeTraits;
use Illuminate\Database\Eloquent\Model;

class ShortCode extends Model
{
    use ShortCodeTraits;
    use CommonTraits;

    /**
     * The attributes that should be mutated to dates.
     *
     * @var array
     */
    protected $dates = [
        'expires_at'
    ];

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'code', 'action', 'expires_at', 'reserved_for_user_id', 'user_id', 'owner_id', 'owner_type'
    ];

    /**
     * Get the owning resource e.g Subscription, Store, Instant Cart
     */
    public function owner()
    {
        return $this->morphTo();
    }

    /*
     *  Returns the authenticated user that created or last updated this shortcode
     */
    public function user()
    {
        return $this->belongsTo('App\User');
    }

    /*
     *  Returns the user allowed to use this shortcode
     */
    public function reservedUser()
    {
        return $this->belongsTo('App\User', 'reserved_for_user_id');
    }

    /** ATTRIBUTES
     *
     *  Note that the "resource_type" is defined within CommonTraits.
     */
    protected $appends = [
        'resource_type', 'dialing_code', 'is_creator', 'is_expired'
    ];

    /*
     *  Returns if the current user created this shortcode
     */
    public function getIsCreatorAttribute()
    {
        $status = ($this->reserved_for_user_id == auth()->user()->id);

        return [
            'status' => $status,
            'description' => $status ? 'This shortcode is reserved for you' : 'This shortcode is not reserved for you'
        ];
    }

    /*
     *  Returns if the shortcode has expired
     */
    public function getIsExpiredAttribute()
    {
        $status = \Carbon\Carbon::parse($this->expires_at)->isPast();

        return [
            'status' => $status,
            'name' => $status ? 'Expired' : 'Not expired',
            'description' => $status ? 'This shortcode is not expired yet' : 'This shortcode has expired'
        ];
    }

    /*
     *  Returns the resource type
     */
    public function getDialingCodeAttribute()
    {
        $main_short_code = config('app.MAIN_SHORT_CODE');

        $code = $this->code;

        if( $this->action == 'payment' ){

            //  Add 2 leading Zero's
            $code = '00'.$this->code;

        }elseif( $this->owner_type == 'instant_cart' ){

            //  Add 1 leading Zero
            $code = '0'.$this->code;

        }

        return '*'.$main_short_code.'*'.$code.'#';
    }

}
