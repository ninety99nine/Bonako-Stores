<?php

namespace App;

use App\Traits\OrderTraits;
use App\Traits\CommonTraits;
use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    use CommonTraits, OrderTraits;

    protected $with = ['status', 'paymentStatus', 'fulfillmentStatus', 'activeCart', 'deliveryLine'];

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $casts = [
        'delivery_verified' => 'boolean',  //  Return the following 1/0 as true/false
    ];

    /**
     * The attributes that should be mutated to dates.
     *
     * @var array
     */
    protected $dates = [
        'delivery_verified_at',
        'request_customer_rating_at',
    ];

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        /*  Basic Info  */
        'number',

        /*  Rating Info  */
        'request_customer_rating_at',

        /*  Status Info  */
        'status_id', 'payment_status_id', 'fulfillment_status_id',

        /*  Cancellation Info  */
        'cancellation_reason',

        /*  Customer Info  */
        'customer_id',

        /*  Delivery Info  */
        'delivery_confirmation_code', 'delivery_verified', 'delivery_verified_at',
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'delivery_confirmation_code',
    ];

    /*
     *  Scope:
     *  Returns orders that are being searched
     */
    public function scopeSearch($query, $searchTerm)
    {
        return $query->where('number', $searchTerm);
    }

    /*
     *  Scope:
     *  Returns orders that are fulfilled
     */
    public function scopeFulfilled($query)
    {
        return $query->where('fulfillment_status', 'fulfilled')->open();
    }

    /*
     *  Scope:
     *  Returns orders that are unfulfilled
     */
    public function scopeUnfulfilled($query)
    {
        return $query->where('fulfillment_status', 'unfulfilled')->open();
    }

    /*
     *  Scope:
     *  Returns orders that are open
     */
    public function scopeOpen($query)
    {
        return $query->where('status', 'open');
    }

    /*
     *  Scope:
     *  Returns orders that are archieved
     */
    public function scopeArchieved($query)
    {
        return $query->where('status', 'archieved');
    }

    /*
     *  Scope:
     *  Returns orders that are cancelled
     */
    public function scopeCancelled($query)
    {
        return $query->where('status', 'cancelled');
    }

    /*
     *  Scope:
     *  Returns orders that are placed by the current user
     */
    public function scopeUserIsCustomer($query, $user = null)
    {
        $user = $user ?? auth()->user();

        return $query->where('customer_id', $user->id);
    }

    /*
     *  Scope:
     *  Returns orders that require a rating
     */
    public function scopeRequireRating($query, $user = null)
    {
        return $query->userIsCustomer($user)->where('request_customer_rating_at', '<=', \Carbon\Carbon::now());
    }

    /**
     *  Returns the associated locations
     */
    public function locations()
    {
        return $this->belongsToMany(Location::class, 'location_order')->latest();
    }

    /**
     *  Returns the received location. This is the original
     *  location that the order was first placed
     */
    public function receivedLocations()
    {
        return $this->locations()->wherePivot('is_shared', 0);
    }

    /**
     *  Returns the shared locations. These are
     *  locations that the order was shared
     */
    public function sharedLocations()
    {
        return $this->locations()->wherePivot('is_shared', 1);
    }

    /**
     *  Returns the carts owned by this order
     */
    public function carts()
    {
        return $this->morphMany(Cart::class, 'owner');
    }

    /**
     *  Returns the active cart owned by this order
     */
    public function activeCart()
    {
        return $this->morphOne(Cart::class, 'owner')->active();
    }

    /**
     *  Returns the order status
     */
    public function status()
    {
        return $this->belongsTo('App\Status');
    }

    /**
     *  Returns the order payment status
     */
    public function paymentStatus()
    {
        return $this->belongsTo('App\Status');
    }

    /**
     *  Returns the order fulfillment status
     */
    public function fulfillmentStatus()
    {
        return $this->belongsTo('App\Status');
    }

    /**
     *  Returns the order customer
     */
    public function customer()
    {
        return $this->belongsTo('App\User', 'customer_id');
    }

    /**
     *  Returns the order delivery line
     */
    public function deliveryLine()
    {
        return $this->hasOne('App\DeliveryLine');
    }

    /** ATTRIBUTES
     *
     *  Note that the "resource_type" is defined within CommonTraits.
     */
    protected $appends = [
        'resource_type'
    ];

    public function setDeliveryVerifiedAttribute($value)
    {
        $this->attributes['delivery_verified'] = (($value == 'true' || $value === '1') ? 1 : 0);
    }

}
