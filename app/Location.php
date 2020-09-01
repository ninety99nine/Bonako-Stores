<?php

namespace App;

use DB;
use App\Traits\CommonTraits;
use App\Traits\LocationTraits;
use Illuminate\Database\Eloquent\Model;

class Location extends Model
{
    use LocationTraits, CommonTraits;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $casts = [
        'online' => 'boolean',                      //  Return the following 1/0 as true/false
        'allow_delivery' => 'boolean',              //  Return the following 1/0 as true/false
        'allow_pickups' => 'boolean',      //  Return the following 1/0 as true/false
        'allow_payments' => 'boolean',              //  Return the following 1/0 as true/false
        'online_payment_methods' => 'array',
        'offline_payment_methods' => 'array',
        'delivery_destinations' => 'array',
        'delivery_days' => 'array',
        'delivery_times' => 'array',
        'pickup_destinations' => 'array',
        'pickup_days' => 'array',
        'pickup_times' => 'array',
    ];

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name', 'abbreviation', 'about_us', 'contact_us', 'call_to_action', 'allow_delivery', 'allow_pickups',
        'delivery_note', 'delivery_flat_fee', 'allow_payments', 'online_payment_methods', 'offline_payment_methods', 'delivery_destinations', 
        'pickup_destinations', 'delivery_days', 'pickup_note', 'pickup_days', 'delivery_times', 'pickup_times', 'online', 
        'offline_message', 'user_id', 'store_id'
    ];

    /*
     *  Returns the store of this location
     */
    public function store()
    {
        return $this->belongsTo('App\Store', 'store_id');
    }

    /*
     *  Returns the users that have been assigned to this store
     */
    public function users()
    {
        return $this->belongsToMany('App\User')->withPivot('type');
    }

    /*
     *  Returns the list of products that belongs to this location
     */
    public function products()
    {
        return $this->belongsToMany('App\Product');
    }

    /*
     *  Returns the list of payment methods used by this location
     */
    public function paymentMethods()
    {
        return $this->belongsToMany('App\PaymentMethod');
    }

    /** ATTRIBUTES
     * 
     *  Note that the "resource_type" is defined within CommonTraits
     * 
     */
    protected $appends = [
        'resource_type',
    ];

    public function setOnlineAttribute($value)
    {
        $this->attributes['online'] = ( ($value == 'true' || $value === '1') ? 1 : 0);
    }

    public function setAllowDeliveryAttribute($value)
    {
        $this->attributes['allow_delivery'] = ( ($value == 'true' || $value === '1') ? 1 : 0);
    }

    public function setAllowCustomerPickupsAttribute($value)
    {
        $this->attributes['allow_pickups'] = ( ($value == 'true' || $value === '1') ? 1 : 0);
    }    

    public function setAllowPaymentsAttribute($value)
    {
        $this->attributes['allow_payments'] = ( ($value == 'true' || $value === '1') ? 1 : 0);
    }

    //  ON DELETE EVENT
    public static function boot()
    {
        parent::boot();

        // before delete() method call this
        static::deleting(function ($location) {

            //  Delete all products
            foreach ($location->products as $product) {
                $product->delete();
            }

            //  Delete all records of users being assigned to this location
            DB::table('location_user')->where(['location_id' => $location->id])->delete();

            //  Delete all records of products being assigned to this location
            DB::table('location_product')->where(['location_id' => $location->id])->delete();

            // do the rest of the cleanup...
        });
    }

}
