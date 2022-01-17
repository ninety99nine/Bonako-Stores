<?php

namespace App;

use Carbon\Carbon;
use Carbon\CarbonInterval;
use Carbon\CarbonInterface;
use App\Traits\OrderTraits;
use App\Traits\CommonTraits;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use \Staudenmeir\EloquentHasManyDeep\HasRelationships;

class Order extends Model
{
    use HasRelationships, CommonTraits, OrderTraits;

    protected $with = ['status', 'paymentStatus', 'deliveryStatus', 'deliveryLine', 'activeCart'];

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $casts = [
        'submitted_by_store_user' => 'boolean',  //  Return the following 1/0 as true/false
        'delivery_verified' => 'boolean',        //  Return the following 1/0 as true/false
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

        /*  Store Submittion Info  */
        'submitted_by_store_user', 'store_user_id',

        /*  Customer Info  */
        'customer_id',

        /*  Delivery Info  */
        'delivery_confirmation_code', 'delivery_verified', 'delivery_verified_at',
        'delivery_verified_by', 'delivery_verified_by_user_id'
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
        return $query->where('number', $searchTerm)
                     ->orWhereHas('customer', function (Builder $query) use ($searchTerm) {
                        $query->search($searchTerm);
                    });;
    }

    /*
     *  Scope:
     *  Returns orders that are being searched
     */
    public function scopeSearchDeliveryConfirmationCode($query, $delivery_confirmation_code)
    {
        return $query->where('delivery_confirmation_code', $delivery_confirmation_code);
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
     *  Returns orders that are placed by the given customer id
     */
    public function scopeUserIsCustomer($query, $customer_id = null)
    {
        return $query->where('customer_id', $customer_id);
    }

    /*
     *  Scope:
     *  Returns orders that require a rating
     */
    public function scopeRequireRating($query, $customer_id = null)
    {
        return $query->userIsCustomer($customer_id)->where('request_customer_rating_at', '<=', \Carbon\Carbon::now());
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
     *  Returns the associated location users
     */
    public function users()
    {
        return $this->hasManyDeep(User::class, ['location_order', Location::class, 'location_user']);
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
        return $this->belongsTo('App\Customer', 'customer_id');
    }

    /**
     *  Returns the order delivery line
     */
    public function deliveryLine()
    {
        return $this->hasOne('App\DeliveryLine');
    }
    /**
     * Get the latest transaction
     */
    public function transaction()
    {
        return $this->morphOne(Transaction::class, 'owner')->latest();
    }

    /**
     * Get the transactions
     */
    public function transactions()
    {
        return $this->morphMany(Transaction::class, 'owner');
    }

    /** ATTRIBUTES
     *
     *  Note that the "resource_type" is defined within CommonTraits.
     */
    protected $appends = [
        'resource_type', 'is_paid', 'is_delivered', 'delivery_verified_description',
        'time_elapsed_to_delivery_verified', 'requires_delivery_confirmation_code'
    ];

    /**
     *  This method returns true/false if the order is paid
     */
    public function getIsPaidAttribute()
    {
        try {

            if( $this->paymentStatus ){
                return $this->paymentStatus->name === 'Paid' ? true : false;
            }
            return false;

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

            if( $this->deliveryStatus ){
                return $this->deliveryStatus->name === 'Delivered' ? true : false;
            }
            return false;

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

            $delivery_verified_by = !empty($this->delivery_verified_by) ? ' by ' . $this->delivery_verified_by : '';

            return $this->delivery_verified
                    ? 'The order delivery was successfully verified at ' . $delivery_verified_at . $delivery_verified_by
                    : 'The order delivery has not been verified';

        } catch (\Exception $e) {

            throw($e);

        }
    }

    /**
     *  This method returns the delivery verified description
     */
    public function getTimeElapsedToDeliveryVerifiedAttribute()
    {
        try {

            if( $this->delivery_verified ){

                //  Set verified datetime
                $delivery_verified_at = Carbon::parse($this->delivery_verified_at);

                //  Set created datetime
                $order_created_at = Carbon::parse($this->created_at);

                $totalDuration = $delivery_verified_at->DiffInSeconds($order_created_at);

                return [
                    'one_entry' => CarbonInterval::seconds($totalDuration)->cascade()->forHumans(['parts' => 1]),
                    'two_entries' => CarbonInterval::seconds($totalDuration)->cascade()->forHumans(['join' => true, 'parts' => 2]),
                ];

            }

            return null;

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

    /**
     *  Returns the coupon allow_usage_limit status and description
     */
    public function getSubmittedByStoreUserAttribute($value)
    {
        return [
            'status' => $value ? true : false,
            'name' => $value ? 'Yes' : 'No',
            'description' => $value ? 'This order was submitted by the store'
                                    : 'This order was submitted by the customer'
        ];
    }

    public function setDeliveryVerifiedAttribute($value)
    {
        $this->attributes['delivery_verified'] = (($value == 'true' || $value === '1') ? 1 : 0);
    }

    public function setSubmittedByStoreUserAttribute($value)
    {
        $this->attributes['submitted_by_store_user'] = (($value == 'true' || $value === '1') ? 1 : 0);
    }

}
