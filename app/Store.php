<?php

namespace App;

use DB;
use Carbon\Carbon;
use App\Traits\StoreTraits;
use App\Traits\CommonTraits;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;

class Store extends Model
{
    use StoreTraits;
    use CommonTraits;

    protected $with = ['visitShortCodes', 'paymentShortCodes', 'myActiveSubscriptions'];

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
        return $query->where('location_user.user_id', '=', $user_id)
                     ->where('stores.user_id', '!=', $user_id)->orWhereNull('stores.user_id');
    }

    /*
     *  Scope:
     *  Returns stores considered to be the user's favourite stores. To implement this, we must
     *  return only stores with locations that have been marked as the user's favourite.
     */
    public function scopeAsFavourite($query, $user_id)
    {
        return $query->whereHas('locations.favourites', function (Builder $query) use ($user_id) {
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
     *  Returns subscriptions of this store
     */
    public function subscriptions()
    {
        return $this->hasMany('App\Subscription');
    }

    /*
     *  Returns the current user's active subscriptions
     *  of this store
     */
    public function myActiveSubscriptions()
    {
        $user_id = auth()->user()->id;

        return $this->subscriptions()->active( $user_id )->asOwner( $user_id );
    }

    /*
     *  Returns the current user's inactive subscriptions
     *  of this store
     */
    public function myInactiveSubscriptions()
    {
        $user_id = auth()->user()->id;

        return $this->subscriptions()->inactive( $user_id )->asOwner( $user_id );
    }

    /*
     *  Returns the short code owned by this store
     *  Only short codes that are not expired are
     *  valid.
     */
    public function shortCodes()
    {
        return $this->morphMany(ShortCode::class, 'owner')
                    ->where('expires_at', '>', Carbon::now());
    }

    /*
     *  Returns the visit short codes owned by this store
     */
    public function visitShortCodes()
    {
        return $this->shortCodes()->where('action', 'visit');
    }

    /*
     *  Returns the payment short codes owned by this store
     */
    public function paymentShortCodes()
    {
        return $this->shortCodes()->where('action', 'payment');
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
        'resource_type', 'has_visit_short_code', 'visit_short_code', 'has_payment_short_code', 'payment_short_code',
        'has_subscribed', 'subscription'
    ];

    /*
     *  Ckecks if we have a visit short code
     */
    public function getHasVisitShortCodeAttribute()
    {
        return collect($this->visitShortCodes)->count() ? true : false;
    }

    /*
     *  Returns the visit short code
     */
    public function getVisitShortCodeAttribute()
    {
        return $this->has_visit_short_code ? collect($this->visitShortCodes)->first()->only(['dialing_code', 'expires_at']) : null;
    }

    /*
     *  Ckecks if we have a payment short code
     */
    public function getHasPaymentShortCodeAttribute()
    {
        return collect($this->paymentShortCodes)->count() ? true : false;
    }

    /*
     *  Returns the payment short code
     */
    public function getPaymentShortCodeAttribute()
    {
        return $this->has_payment_short_code ? collect($this->paymentShortCodes)->first()->only(['dialing_code', 'expires_at']) : null;
    }

    /*
     *  Ckecks if we have an active subscription
     */
    public function getHasSubscribedAttribute()
    {
        return collect($this->myActiveSubscriptions)->count() ? true : false;
    }

    /*
     *  Returns the payment short code
     */
    public function getSubscriptionAttribute()
    {
        return $this->has_subscribed ? collect($this->myActiveSubscriptions)->first()
                                        ->only(['id', 'subscription_plan_id', 'start_at', 'end_at']) : null;
    }

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

                //  Delete all subscription
                foreach ($store->subscriptions as $subscription) {
                    $subscription->delete();
                }

                //  Delete all subscription
                foreach ($store->subscriptions as $subscription) {
                    $subscription->delete();
                }

                //  Delete all instant carts
                $store->instantCarts()->delete();

                //  Delete all coupons
                $store->coupons()->delete();

                //  Delete all ratings
                $store->ratings()->delete();

                //  Expire short codes
                $store->shortCodes()->update([
                    'expires_at' => Carbon::now()
                ]);

                // do the rest of the cleanup...
            });
        } catch (\Exception $e) {
            throw($e);
        }
    }
}
