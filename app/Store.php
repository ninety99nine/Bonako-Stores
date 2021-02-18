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

    protected $with = ['visitShortCode', 'paymentShortCode', 'myActiveSubscription'];

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $casts = [
        'online' => 'boolean',                      //  Return the following 1/0 as true/false
        'allow_sending_merchant_sms' => 'boolean',  //  Return the following 1/0 as true/false
    ];

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name', 'online', 'offline_message', 'hex_color', 'allow_sending_merchant_sms', 'user_id'
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
     *  Returns subscription of this store
     */
    public function subscription()
    {
        return $this->hasOne('App\Subscription')->latest();
    }

    /*
     *  Returns the current user's active subscriptions of this store
     */
    public function myActiveSubscription()
    {
        $user_id = auth()->user()->id;

        return $this->subscription()->active()->asOwner($user_id);
    }

    /*
     *  Returns the short codes owned by this store
     *  Only short codes that are not expired are
     *  valid.
     */
    public function shortCodes()
    {
        return $this->morphMany(ShortCode::class, 'owner')->where('expires_at', '>', Carbon::now())->latest();
    }

    /*
     *  Returns the short code owned by this store
     *  Only short codes that are not expired are
     *  valid.
     */
    public function shortCode()
    {
        return $this->morphOne(ShortCode::class, 'owner')->where('expires_at', '>', Carbon::now())->latest();
    }

    /*
     *  Returns the visit short codes owned by this store
     */
    public function visitShortCode()
    {
        return $this->shortCode()->where('action', 'visit');
    }

    /*
     *  Returns the payment short codes owned by this store
     */
    public function paymentShortCode()
    {
        return $this->shortCode()->where('action', 'payment');
    }

    /*
     *  Returns locations of this store
     */
    public function locations()
    {
        return $this->hasMany('App\Location', 'store_id');
    }

    /** ATTRIBUTES
     *
     *  Note that the "resource_type" is defined within CommonTraits.
     */
    protected $appends = [
        'resource_type'
    ];

    public function setOnlineAttribute($value)
    {
        $this->attributes['online'] = (($value == 'true' || $value === '1') ? 1 : 0);
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
            static::deleting(function ($store) {

                //  Delete all locations
                foreach ($store->locations as $location) {
                    $location->delete();
                }

                //  Delete all subscription
                foreach ($store->subscriptions as $subscription) {
                    $subscription->delete();
                }

                //  Expire short codes (CommanTraits)
                $store->expireShortCodes();

                // do the rest of the cleanup...
            });
        } catch (\Exception $e) {
            throw($e);
        }
    }
}
