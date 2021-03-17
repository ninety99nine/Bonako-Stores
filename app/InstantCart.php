<?php

namespace App;

use DB;
use Carbon\Carbon;
use App\Traits\CommonTraits;
use App\Traits\InstantCartTraits;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;

class InstantCart extends Model
{
    use CommonTraits, InstantCartTraits;

    protected $with = ['visitShortCode', 'paymentShortCode', 'cart'];

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [

        /*  Status  */
        'active',

        /*  Basic Info  */
        'name', 'description',

        /*  Location Info  */
        'location_id'

    ];

    protected $allowedFilters = [];

    protected $allowedOrderableColumns = [];

    /**
     *  Scope:
     *  Returns instant carts that are being searched
     */
    public function scopeSearch($query, $searchTerm)
    {
        return $query->where('name', 'like', '%'.$searchTerm.'%');
    }

    /**
     *  Scope:
     *  Returns instant carts that are active
     */
    public function scopeActive($query)
    {
        return $query->whereActive('1');
    }

    /**
     *  Scope:
     *  Returns instant carts that are not active
     */
    public function scopeInActive($query)
    {
        return $query->whereActive('0');
    }

    /**
     *  Scope:
     *  Returns instant carts that are expired
     */
    public function scopeExpired($query)
    {
        return $query->doesntHave('visitShortCode');
    }

    /**
     *  Scope:
     *  Returns instant carts that offer free delivery
     *  Free delivery is available if the cart offers
     *  it directly or indirectly via its coupon lines
     */
    public function scopeOffersFreeDelivery($query)
    {
        return $query->whereHas('cart', function (Builder $query) {
            $query->offersFreeDelivery();
        });
    }

    /**
     *  Returns the owning location
     */
    public function location()
    {
        return $this->belongsTo('App\Location', 'location_id');
    }

    /**
     *  Returns the cart owned by this instant cart
     */
    public function cart()
    {
        return $this->morphOne(Cart::class, 'owner');
    }

    /**
     *  Returns subscriptions of this instant cart
     */
    public function subscriptions()
    {
        return $this->morphMany(Subscription::class, 'owner')->latest();
    }

    /**
     *  Returns subscription of this instant cart
     */
    public function subscription()
    {
        return $this->morphOne(Subscription::class, 'owner')->latest();
    }

    /**
     *  Returns the short codes owned by this instant cart
     *  Only short codes that are not expired are valid.
     */
    public function shortCodes()
    {
        return $this->morphMany(ShortCode::class, 'owner')->where('expires_at', '>', Carbon::now())->latest();
    }

    /**
     *  Returns the short code owned by this instant cart
     *  Only short codes that are not expired are valid.
     */
    public function shortCode()
    {
        return $this->morphOne(ShortCode::class, 'owner')->where('expires_at', '>', Carbon::now())->latest();
    }

    /**
     *  Returns the visit short codes owned by this instant cart
     */
    public function visitShortCode()
    {
        return $this->shortCode()->where('action', 'visit');
    }

    /**
     *  Returns the payment short codes owned by this instant cart
     */
    public function paymentShortCode()
    {
        return $this->shortCode()->where('action', 'payment');
    }

    /** ATTRIBUTES
     *
     *  Note that the "resource_type" is defined within CommonTraits
     *
     */
    protected $appends = [
        'resource_type'
    ];

    /**
     *  Returns the instant cart active status and description
     */
    public function getActiveAttribute($value)
    {
        return [
            'status' => $value ? true : false,
            'name' => $value ? 'Active' : 'Inactive',
            'description' => $value ? 'This instant cart is active'
                                    : 'This instant cart is not active'
        ];
    }

    public function setActiveAttribute($value)
    {
        if( is_array($value) ){
            $this->attributes['active'] = (in_array($value['status'], ['true', true, '1', 1]) ? 1 : 0);
        }else{
            $this->attributes['active'] = (($value == 'true' || $value == '1') ? 1 : 0);
        }
    }

    //  ON DELETE EVENT
    public static function boot()
    {
        try {
            parent::boot();

            // before delete() method call this
            static::deleting(function ($instant_cart) {

                //  Delete cart
                $instant_cart->cart()->delete();

                //  Expire short codes (CommanTraits)
                $instant_cart->expireShortCodes();

                // do the rest of the cleanup...
            });

        } catch (\Exception $e) {
            throw($e);
        }
    }

}
