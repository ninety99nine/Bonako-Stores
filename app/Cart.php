<?php

namespace App;

use App\Traits\CartTraits;
use App\Traits\CommonTraits;
use Illuminate\Database\Eloquent\Model;

class Cart extends Model
{
    use CartTraits, CommonTraits;

    protected $with = ['currency', 'itemLines', 'couponLines'];

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [

        /*  Cart Info  */
        'active', 'sub_total', 'coupon_total', 'sale_discount_total', 'coupon_and_sale_discount_total', 
        'delivery_fee', 'grand_total', 'total_items', 'total_unique_items',

        /*  Currency Info  */
        'currency_id',

        /*  Location Info  */
        'location_id',

        /*  Ownership Information  */
        'owner_id', 'owner_type'

    ];

    /*
     *  Scope:
     *  Returns products that are active (Visible products)
     */
    public function scopeActive($query)
    {
        return $query->whereActive('1');
    }

    /*
     *  Scope:
     *  Returns products that are not active (Hidden products)
     */
    public function scopeInActive($query)
    {
        return $query->whereActive('0');
    }

    /**
     * Get the owning resource e.g Order
     */
    public function owner()
    {
        return $this->morphTo();
    }

    /**
     *  Returns the currency
     */
    public function currency()
    {
        return $this->belongsTo('App\Currency');
    }

    /**
     *  Returns the cart item lines
     */
    public function itemLines()
    {
        return $this->hasMany(ItemLine::class);
    }

    /**
     *  Returns the cart coupon lines
     */
    public function couponLines()
    {
        return $this->hasMany(CouponLine::class);
    }
}
