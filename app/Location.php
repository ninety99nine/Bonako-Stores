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

    protected $with = ['currency'];

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $casts = [
        'online' => 'boolean',                      //  Return the following 1/0 as true/false
        'allow_delivery' => 'boolean',              //  Return the following 1/0 as true/false
        'allow_pickups' => 'boolean',               //  Return the following 1/0 as true/false
        'allow_payments' => 'boolean',              //  Return the following 1/0 as true/false
        'allow_sending_merchant_sms' => 'boolean',           //  Return the following 1/0 as true/false

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
        'name', 'abbreviation', 'about_us', 'contact_us', 'call_to_action', 'online', 'offline_message',
        'allow_delivery', 'delivery_note', 'delivery_flat_fee', 'delivery_destinations', 'delivery_days',
        'delivery_times', 'allow_pickups', 'pickup_note', 'pickup_destinations', 'pickup_days',
        'pickup_times', 'allow_payments', 'online_payment_methods', 'offline_payment_methods',
        'currency_id', 'orange_money_merchant_code', 'minimum_stock_quantity',
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
     *  Returns the currency
     */
    public function currency()
    {
        return $this->belongsTo('App\Currency');
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
        return $this->hasMany('App\InstantCart')->with(['products', 'coupons'])->latest();
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

                //  Delete all location
                $location->orders()->delete();

                //  Delete all location
                $location->coupons()->delete();

                //  Delete all ratings
                $location->ratings()->delete();

                //  Delete all favourites
                $location->favourites()->delete();

                //  Delete all instant carts
                $location->instantCarts()->delete();

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
