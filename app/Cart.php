<?php

namespace App;

use App\Traits\CartTraits;
use App\Traits\CommonTraits;
use Illuminate\Database\Eloquent\Model;

class Cart extends Model
{
    use CartTraits, CommonTraits;

    protected $with = ['itemLines', 'couponLines'];

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
        'currency',

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

    /** ATTRIBUTES
     *
     *  Note that the "resource_type" is defined within CommonTraits
     *
     */
    protected $appends = [
        'resource_type', ''
    ];

    /**
     *  Returns the cart currency code and symbol
     */
    public function getCurrencyAttribute($currency_code)
    {
        return $this->unpackCurrency($currency_code);
    }

    /**
     *  Returns the cart sub total
     */
    public function getSubTotalAttribute($amount)
    {
        return $this->convertToMoney($this->currency, $amount);
    }

    /**
     *  Returns the cart coupon total
     */
    public function getCouponTotalAttribute($amount)
    {
        return $this->convertToMoney($this->currency, $amount);
    }

    /**
     *  Returns the sale discount total
     */
    public function getSaleDiscountTotalAttribute($amount)
    {
        return $this->convertToMoney($this->currency, $amount);
    }

    /**
     *  Returns the coupon and sale discount total
     */
    public function getCouponAndSaleDiscountTotalAttribute($amount)
    {
        return $this->convertToMoney($this->currency, $amount);
    }

    /**
     *  Returns the delivery fee
     */
    public function getDeliveryFeeAttribute($amount)
    {
        return $this->convertToMoney($this->currency, $amount);
    }

    /**
     *  Returns the delivery fee
     */
    public function getGrandTotalAttribute($amount)
    {
        return $this->convertToMoney($this->currency, $amount);
    }

    public function setCurrencyAttribute($value)
    {
        $this->attributes['currency'] = is_array($value) ? $value['code'] : $value;
    }

    public function setSubTotalAttribute($value)
    {
        $this->attributes['sub_total'] = is_array($value) ? $value['amount'] : $value;
    }

    public function setCouponTotalAttribute($value)
    {
        $this->attributes['coupon_total'] = is_array($value) ? $value['amount'] : $value;
    }

    public function setSaleDiscountTotalAttribute($value)
    {
        $this->attributes['sale_discount_total'] = is_array($value) ? $value['amount'] : $value;
    }

    public function setCouponAndSaleDiscountTotalAttribute($value)
    {
        $this->attributes['coupon_and_sale_discount_total'] = is_array($value) ? $value['amount'] : $value;
    }

    public function setDeliveryFeeAttribute($value)
    {
        $this->attributes['delivery_fee'] = is_array($value) ? $value['amount'] : $value;
    }

    public function setGrandTotalAttribute($value)
    {
        $this->attributes['grand_total'] = is_array($value) ? $value['amount'] : $value;
    }

}
