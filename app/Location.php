<?php

namespace App;

use DB;
use App\Traits\CommonTraits;
use App\Traits\LocationTraits;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;

class Location extends Model
{
    use LocationTraits;
    use CommonTraits;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $casts = [
        'online' => 'boolean',                      //  Return the following 1/0 as true/false
        'allow_free_delivery' => 'boolean',         //  Return the following 1/0 as true/false
        'allow_delivery' => 'boolean',              //  Return the following 1/0 as true/false
        'allow_pickups' => 'boolean',               //  Return the following 1/0 as true/false
        'allow_payments' => 'boolean',              //  Return the following 1/0 as true/false
        'allow_sending_merchant_sms' => 'boolean',  //  Return the following 1/0 as true/false

        'delivery_destinations' => 'array',
        'delivery_days' => 'array',
        'delivery_times' => 'array',
        'pickup_destinations' => 'array',
        'pickup_days' => 'array',
        'pickup_times' => 'array',
        'online_payment_methods' => 'array',
        'offline_payment_methods' => 'array',
    ];

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name', 'abbreviation', 'about_us', 'contact_us', 'call_to_action', 'online', 'allow_free_delivery',
        'offline_message', 'allow_delivery', 'delivery_note', 'delivery_flat_fee', 'delivery_destinations',
        'delivery_days', 'delivery_times', 'allow_pickups', 'pickup_note', 'pickup_destinations', 'pickup_days',
        'pickup_times', 'allow_payments', 'online_payment_methods', 'offline_payment_methods',
        'currency', 'orange_money_merchant_code', 'minimum_stock_quantity',
        'allow_sending_merchant_sms', 'user_id', 'store_id',
    ];

    /**
     *  Scope:
     *  Returns locations that are being searched
     */
    public function scopeSearch($query, $searchTerm)
    {
        return $query->where('name', $searchTerm);
    }

    /**
     *  Scope:
     *  Returns locations marked as the user's favourite locations
     */
    public function scopeAsFavourite($query, $user_id)
    {
        return $query->whereHas('favourites', function (Builder $query) use ($user_id){
            $query->where('user_id', $user_id);
        });
    }

    /**
     *  Returns the favourites of this location
     */
    public function favourites()
    {
        return $this->hasMany('App\Favourite');
    }

    /**
     *  Returns the store of this location
     */
    public function store()
    {
        return $this->belongsTo('App\Store', 'store_id');
    }

    /**
     *  Returns the users that have been assigned to this location
     */
    public function users()
    {
        return $this->belongsToMany('App\User')->withPivot('type');
    }

    /**
     *  Returns the products assigned to this location.
     */
    public function products()
    {
        return $this->morphToMany('App\Product', 'owner', 'product_allocations')
                    ->orderBy('product_allocations.arrangement')
                    ->withTimestamps()->withPivot('quantity')
                    ->isNotVariation();
    }

    /**
     *  Returns the associated orders
     */
    public function orders()
    {
        return $this->belongsToMany(Order::class, 'location_order')->latest();
    }

    /**
     *  Returns the received order. This is the original
     *  order that the order was first placed
     */
    public function receivedOrders()
    {
        return $this->orders()->wherePivot('is_shared', 0);
    }

    /**
     *  Returns the shared orders. These are
     *  orders that the order was shared
     */
    public function sharedOrders()
    {
        return $this->orders()->wherePivot('is_shared', 1);
    }

    /**
     *  Returns the instant carts that have been assigned to this location
     */
    public function instantCarts()
    {
        return $this->hasMany('App\InstantCart')->latest();
    }

    /**
     *  Returns coupons for this store.
     */
    public function coupons()
    {
        return $this->hasMany('App\Coupon')->latest();
    }

    /*
     *  Returns the list of payment methods used by this location
     */
    public function paymentMethods()
    {
        return $this->belongsToMany('App\PaymentMethod');
    }

    /**
     *  Get the location ratings.
     */
    public function ratings()
    {
        return $this->hasMany('App\LocationRating');
    }

    /** ATTRIBUTES
     *
     *  Note that the "resource_type" is defined within CommonTraits.
     */
    protected $appends = [
        'resource_type',
    ];

    /**
     *  Returns the location online status and description
     */
    public function getOnlineAttribute($value)
    {
        return [
            'status' => $value ? true : false,
            'name' => $value ? 'Online' : 'Offline',
            'description' => $value ? 'This location is online and can be visited by customers'
                                    : 'This location is offline and cannot be visited by customers'
        ];
    }

    /**
     *  Returns the location allow delivery status and description
     */
    public function getAllowDeliveryAttribute($value)
    {
        return [
            'status' => $value ? true : false,
            'name' => $value ? 'Yes' : 'No',
            'description' => $value ? 'This location supports delivery of orders'
                                    : 'This location does not support delivery of orders'
        ];
    }

    /**
     *  Returns the location allow free delivery status and description
     */
    public function getAllowFreeDeliveryAttribute($value)
    {
        return [
            'status' => $value ? true : false,
            'name' => $value ? 'Yes' : 'No',
            'description' => $value ? 'This location supports free delivery of orders'
                                    : 'This location does not support free delivery of orders'
        ];
    }

    /**
     *  Returns the location allow pickups status and description
     */
    public function getAllowPickupsAttribute($value)
    {
        return [
            'status' => $value ? true : false,
            'name' => $value ? 'Yes' : 'No',
            'description' => $value ? 'This location allows customers to pickup their orders'
                                    : 'This location does not allow customers to pickup their orders'
        ];
    }

    /**
     *  Returns the location allow payments status and description
     */
    public function getAllowPaymentsAttribute($value)
    {
        return [
            'status' => $value ? true : false,
            'name' => $value ? 'Yes' : 'No',
            'description' => $value ? 'This location supports online payment of orders'
                                    : 'This location does not support online payment of orders'
        ];
    }

    /**
     *  Returns the location allow sending merchant sms status and description
     */
    public function getAllowSendingMerchantSmsAttribute($value)
    {
        return [
            'status' => $value ? true : false,
            'name' => $value ? 'Yes' : 'No',
            'description' => $value ? 'This location supports sending sms alerts to merchant mobile number'
                                    : 'This location does not support sending sms alerts to merchant mobile number'
        ];
    }

    /**
     *  Returns the location currency code and symbol
     */
    public function getCurrencyAttribute($currency_code)
    {
        return $this->unpackCurrency($currency_code);
    }

    /**
     *  Returns the unit regular price
     */
    public function getDeliveryFlatFeeAttribute($amount)
    {
        return $this->convertToMoney($this->currency, $amount);
    }

    public function getOnlinePaymentMethodsAttribute($value)
    {
        //  Convert to array
        return is_null($value) ? [] : json_decode($value, true);
    }

    public function getOfflinePaymentMethodsAttribute($value)
    {
        //  Convert to array
        return is_null($value) ? [] : json_decode($value, true);
    }

    public function getDeliveryDestinationsAttribute($value)
    {
        $destinations = is_null($value) ? [] : json_decode($value, true);

        return collect($destinations)->map(function($destination){

            //  Convert cost to money
            $destination['cost'] =  $this->convertToMoney($this->currency, $destination['cost']);

            //  Allow free delivery status value
            $value = $destination['allow_free_delivery'];

            $destination['allow_free_delivery'] = [
                'status' => $value ? true : false,
                'name' => $value ? 'Yes' : 'No',
                'description' => $value ? 'This destination supports free delivery of orders'
                                        : 'This destination does not support free delivery of orders'
            ];

            //  Returnt the destination
            return $destination;

        })->toArray();

        $amount = $this->is_free['status'] ? 0 : $value;

        return $this->convertToMoney($this->currency, $amount);

        //  Convert to array
        return is_null($value) ? [] : json_decode($value, true);
    }

    public function getDeliveryDaysAttribute($value)
    {
        //  Convert to array
        return is_null($value) ? [] : json_decode($value, true);
    }

    public function getDeliveryTimesAttribute($value)
    {
        //  Convert to array
        return is_null($value) ? [] : json_decode($value, true);
    }

    public function getPickupDestinationsAttribute($value)
    {
        //  Convert to array
        return is_null($value) ? [] : json_decode($value, true);
    }

    public function getPickupDaysAttribute($value)
    {
        //  Convert to array
        return is_null($value) ? [] : json_decode($value, true);
    }

    public function getPickupTimesAttribute($value)
    {
        //  Convert to array
        return is_null($value) ? [] : json_decode($value, true);
    }

    public function setOnlineAttribute($value)
    {
        $this->attributes['online'] = (($value == 'true' || $value === '1') ? 1 : 0);
    }

    public function setAllowDeliveryAttribute($value)
    {
        $this->attributes['allow_delivery'] = (($value == 'true' || $value === '1') ? 1 : 0);
    }

    public function setAllowCustomerPickupsAttribute($value)
    {
        $this->attributes['allow_pickups'] = (($value == 'true' || $value === '1') ? 1 : 0);
    }

    public function setAllowPaymentsAttribute($value)
    {
        $this->attributes['allow_payments'] = (($value == 'true' || $value === '1') ? 1 : 0);
    }

    public function setAllowSendingMerchantSmsAttribute($value)
    {
        $this->attributes['allow_sending_merchant_sms'] = (($value == 'true' || $value === '1') ? 1 : 0);
    }

    //  ON DELETE EVENT
    public static function boot()
    {
        try {
            parent::boot();

            // before delete() method call this
            static::deleting(function ($location) {

                //  Delete all products
                foreach ($location->products as $product) {
                    $product->delete();
                }

                //  Delete all instant carts
                foreach ($location->instantCarts as $instantCart) {
                    $instantCart->delete();
                }

                //  Delete all orders
                foreach ($location->orders as $order) {
                    $order->delete();
                }

                //  Delete all coupons
                $location->coupons()->delete();

                //  Delete all ratings
                $location->ratings()->delete();

                //  Delete all favourites
                $location->favourites()->delete();

                //  Delete all records of users being assigned to this location
                DB::table('location_user')->where(['location_id' => $location->id])->delete();

                //  Delete all records of products being assigned to this location
                DB::table('product_allocations')->where(['owner_id' => $location->id, 'owner_type' => 'location'])->delete();

                // do the rest of the cleanup...
            });
        } catch (\Exception $e) {
            throw($e);
        }
    }
}
