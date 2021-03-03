<?php

namespace App;

use Carbon\Carbon;
use App\Traits\OrderTraits;
use App\Traits\CommonTraits;
use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    use CommonTraits, OrderTraits;

    protected $with = ['status', 'paymentStatus', 'deliveryStatus', 'activeCart', 'deliveryLine', 'paymentShortCode', 'transaction'];

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
        'status_id', 'payment_status_id', 'delivery_status_id',

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
     *  Returns orders that are delivered
     */
    public function scopeDelivered($query)
    {
        return $query->where('delivery_status', 'delivered')->open();
    }

    /*
     *  Scope:
     *  Returns orders that are undelivered
     */
    public function scopeUndelivered($query)
    {
        return $query->where('delivery_status', 'undelivered')->open();
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
     *  Returns the order delivery status
     */
    public function deliveryStatus()
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

    /**
     *  Returns the short codes owned by this order
     *  Only short codes that are not expired are
     *  valid.
     */
    public function shortCodes()
    {
        return $this->morphMany(ShortCode::class, 'owner')->where('expires_at', '>', Carbon::now())->latest();
    }

    /**
     *  Returns the short code owned by this order
     *  Only short codes that are not expired are
     *  valid.
     */
    public function shortCode()
    {
        return $this->morphOne(ShortCode::class, 'owner')->where('expires_at', '>', Carbon::now())->latest();
    }

    /**
     *  Returns the payment short codes owned by this order
     */
    public function paymentShortCode()
    {
        return $this->shortCode()->where('action', 'payment');
    }

    /**
     * Get the transaction
     */
    public function transaction()
    {
        return $this->morphOne(Transaction::class, 'owner');
    }

    /** ATTRIBUTES
     *
     *  Note that the "resource_type" is defined within CommonTraits.
     */
    protected $appends = [
        'resource_type', 'is_paid', 'is_delivered', 'delivery_verified_description', 'requires_delivery_confirmation_code'
    ];

    /**
     *  This method returns true/false if the order is paid
     */
    public function getIsPaidAttribute()
    {
        try {

            return $this->paymentStatus->name === 'Paid' ? true : false;

        } catch (\Exception $e) {

            throw($e);

        }
    }

    /**
     *  This method returns true/false if the order is delivered
     */
    public function getIsDeliveredAttribute()
    {
        try {

            return $this->deliveryStatus->name === 'Delivered' ? true : false;

        } catch (\Exception $e) {

            throw($e);

        }
    }

    /**
     *  This method returns the delivery verified description
     */
    public function getDeliveryVerifiedDescriptionAttribute()
    {
        try {

            if( $this->delivery_verified ){

                //  Set verified datetime
                $delivery_verified_at = Carbon::parse($this->delivery_verified_at)->format('H:i d M Y');

            }

            return $this->delivery_verified
                    ? 'The order delivery was successfully verified at ' . $delivery_verified_at
                    : 'The order delivery has not been verified';

        } catch (\Exception $e) {

            throw($e);

        }
    }

    /**
     *  This method returns true/false if the order requires a delivery confirmation code
     */
    public function getRequiresDeliveryConfirmationCodeAttribute()
    {
        try {

            return !empty($this->delivery_confirmation_code) ? true : false;

        } catch (\Exception $e) {

            throw($e);

        }
    }

    public function setDeliveryVerifiedAttribute($value)
    {
        $this->attributes['delivery_verified'] = (($value == 'true' || $value === '1') ? 1 : 0);
    }

}
