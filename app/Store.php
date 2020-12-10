<?php

namespace App;

use App\Traits\CommonTraits;
use App\Traits\StoreTraits;
use DB;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;

class Store extends Model
{
    use StoreTraits;
    use CommonTraits;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $casts = [
        'online' => 'boolean',  //  Return the following 1/0 as true/false
    ];

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name', 'online', 'offline_message', 'user_id', 'currency', 'minimum_stock_quantity',
    ];

    /*
     *  Scope:
     *  Returns stores that are being searched
     */
    public function scopeSearch($query, $searchTerm)
    {
        return $query->where('name', 'like', '%'.$searchTerm.'%');
    }

    /*
     *  Scope:
     *  Returns stores that are active (Visible stores)
     */
    public function scopeActive($query)
    {
        return $query->whereActive('1');
    }

    /*
     *  Scope:
     *  Returns stores that are not active (Hidden stores)
     */
    public function scopeInActive($query)
    {
        return $query->whereActive('0');
    }

    /*
     *  Scope:
     *  Returns stores where user is the owner. This means that the
     *  stores were created by the user.
     */
    public function scopeAsOwner($query, $user_id)
    {
        return $query->where('stores.user_id', '=', $user_id);
    }

    /*
     *  Scope:
     *  Returns stores where user is not the owner. This means that
     *  these stores have been shared with the user.
     */
    public function scopeAsNonOwner($query, $user_id)
    {
        return $query->where('store_user.user_id', '=', $user_id)
                     ->where('stores.user_id', '!=', $user_id)->orWhereNull('stores.user_id');
    }

    /*
     *  Scope:
     *  Returns stores where user is the store admin
     */
    public function scopeAsAdmin($query, $user_id)
    {
        return $query->where('store_user.type', '=', 'admin')
                     ->where('store_user.user_id', '=', $user_id);
    }

    /*
     *  Scope:
     *  Returns stores marked as the user's favourite stores
     */
    public function scopeAsFavourite($query, $user_id)
    {
        return $query->whereHas('favourites', function (Builder $query) use ($user_id){
            $query->where('user_id', $user_id);
        });
    }

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
     *  Returns the users that have been assigned to this store
     */
    public function favourites()
    {
        return $this->hasMany('App\Favourite');
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
     *  Returns coupons for this store.
     */
    public function coupons()
    {
        return $this->hasMany('App\Coupon', 'store_id');
    }

    /*
     *  Returns the instant carts that have been assigned to this store
     */
    public function instantCarts()
    {
        return $this->hasMany('App\InstantCart')->with(['products', 'coupons'])->latest();
    }

    /**
     *  Get the store ratings.
     */
    public function ratings()
    {
        return $this->hasMany('App\StoreRating');
    }

    /** ATTRIBUTES
     *
     *  Note that the "resource_type" is defined within CommonTraits.
     */
    protected $appends = [
        'resource_type',
    ];

    public function setOnlineAttribute($value)
    {
        $this->attributes['online'] = (($value == 'true' || $value === '1') ? 1 : 0);
    }

    //  ON DELETE EVENT
    public static function boot()
    {
        try {
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
        } catch (\Exception $e) {
            throw($e);
        }
    }
}
