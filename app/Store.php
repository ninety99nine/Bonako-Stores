<?php

namespace App;

use DB;
use App\Traits\StoreTraits;
use App\Traits\CommonTraits;
use Illuminate\Database\Eloquent\Model;

class Store extends Model
{
    use StoreTraits, CommonTraits;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $casts = [
        'currency' => 'array',
        'online' => 'boolean',  //  Return the following 1/0 as true/false
    ];

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name', 'online', 'offline_message', 'user_id', 'currency', 'minimum_stock_quantity'
    ];

    /*
     *  Returns the user that created this store
     */
    public function owner()
    {
        return $this->belongsTo('App\User', 'user_id');
    }

    /*
     *  Returns the users that have been assigned to this store
     */
    public function users()
    {
        return $this->belongsToMany('App\User')->withPivot('type');
    }

    /*
     *  Returns locations of this store
     */
    public function locations()
    {
        return $this->hasMany('App\Location', 'store_id');
    }

    /*
     *  Returns locations of this store
     */
    public function products()
    {
        return $this->hasMany('App\Product', 'store_id');
    }

    /**
     *  Returns coupons for this store
     */
    public function coupons()
    {
        return $this->hasMany('App\Coupon', 'store_id');
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

    //  ON DELETE EVENT
    public static function boot()
    {
        parent::boot();

        // before delete() method call this
        static::deleting(function ($store) {

            //  Delete all locations
            foreach ($store->locations as $location) {
                $location->delete();
            }

            //  Delete all products
            foreach ($store->products as $product) {
                $product->delete();
            }

            //  Delete all records of users being assigned to this store
            DB::table('store_user')->where(['store_id' => $store->id])->delete();

            // do the rest of the cleanup...
        });
    }

}
