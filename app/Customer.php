<?php

namespace App;

use App\Traits\CommonTraits;
use App\Traits\CustomerTraits;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;

class Customer extends Model
{
    use CustomerTraits, CommonTraits;

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $with = ['user'];

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'user_id',

        /**********************************
         *  CUSTOMER DATA ON CHECKOUT     *
         *********************************/
        'total_coupons_used_on_checkout', 'total_instant_carts_used_on_checkout', 'total_adverts_used_on_checkout',
        'total_orders_placed_by_customer_on_checkout', 'total_orders_placed_by_store_on_checkout',
        'total_free_delivery_on_checkout',

        'grand_total_on_checkout', 'sub_total_on_checkout', 'sale_discount_total_on_checkout',
        'coupon_total_on_checkout', 'coupon_and_sale_discount_total_on_checkout',
        'delivery_fee_on_checkout', 'total_items_on_checkout',
        'total_unique_items_on_checkout',

        /**********************************
         *  CUSTOMER DATA ON CONVERSION   *
         *********************************/
        'total_coupons_used_on_conversion', 'total_instant_carts_used_on_conversion', 'total_adverts_used_on_conversion',
        'total_orders_placed_by_customer_on_conversion', 'total_orders_placed_by_store_on_conversion',
        'total_free_delivery_on_conversion',

        'grand_total_on_conversion', 'sub_total_on_conversion', 'sale_discount_total_on_conversion',
        'coupon_total_on_conversion', 'coupon_and_sale_discount_total_on_conversion',
        'delivery_fee_on_conversion', 'total_items_on_conversion',
        'total_unique_items_on_conversion',

        'location_id'
    ];

    /**
     *  Scope:
     *  Returns customers that are being searched
     */
    public function scopeSearch($query, $searchTerm)
    {
        return $query->whereHas('user', function (Builder $query) use ($searchTerm) {
            $query->search($searchTerm);
        });
    }

    /**
     *  Scope:
     *  Returns customers that have orders
     */
    public function scopeHasOrdersPlacedByCustomer($query)
    {
        return $query->where('total_orders_placed_by_customer_on_checkout', '>', '0')
                     ->orWhere('total_orders_placed_by_customer_on_conversion', '>', '0');
    }

    /**
     *  Scope:
     *  Returns customers that have orders
     */
    public function scopeHasOrdersPlacedByStore($query)
    {
        return $query->where('total_orders_placed_by_store_on_checkout', '>', '0')
                     ->orWhere('total_orders_placed_by_store_on_conversion', '>', '0');
    }

    /**
     *  Scope:
     *  Returns customers that have used coupons
     */
    public function scopeHasUsedCoupons($query)
    {
        return $query->where('total_coupons_used_on_checkout', '>', '0')
                     ->orWhere('total_coupons_used_on_conversion', '>', '0');
    }

    /**
     *  Scope:
     *  Returns customers that have used instant carts
     */
    public function scopeHasUsedInstantCarts($query)
    {
        return $query->where('total_instant_carts_used_on_checkout', '>', '0')
                     ->orWhere('total_instant_carts_used_on_conversion', '>', '0');
    }

    /**
     *  Scope:
     *  Returns customers that have used adverts
     */
    public function scopeHasUsedAdverts($query)
    {
        return $query->where('total_adverts_used_on_checkout', '>', '0')
                     ->orWhere('total_adverts_used_on_conversion', '>', '0');
    }

    /**
     *  Scope:
     *  Returns customers that have used adverts
     */
    public function scopeHasOrdersWithFreeDelivery($query)
    {
        return $query->where('total_free_delivery_on_checkout', '>', '0')
                     ->orWhere('total_free_delivery_on_conversion', '>', '0');
    }

    /*
     *  Returns the location of this customer
     */
    public function location()
    {
        return $this->belongsTo('App\Location', 'location_id');
    }

    /*
     *  Returns the user of this customer
     */
    public function user()
    {
        return $this->belongsTo('App\User');
    }

    /*
     *  Returns the orders of this customer
     */
    public function orders()
    {
        return $this->hasMany('App\Order');
    }

    /** ATTRIBUTES
     *
     *  Note that the "resource_type" is defined within CommonTraits
     *
     */
    protected $appends = [
        'resource_type', 'total_orders_placed_on_checkout', 'total_orders_placed_on_conversion'
    ];


    /**********************************
     *  CUSTOMER DATA ON CHECKOUT     *
    *********************************/

    /**
     *  Returns the total_orders_placed_on_checkout
     */
    public function getTotalOrdersPlacedOnCheckoutAttribute($amount)
    {
        return $this->total_orders_placed_by_customer_on_checkout + $this->total_orders_placed_by_store_on_checkout;
    }

    /**
     *  Returns the grand_total_on_checkout
     */
    public function getGrandTotalOnCheckoutAttribute($amount)
    {
        return $this->convertToMoney($this->unpackCurrency('BWP'), $amount);
    }

    /**
     *  Returns the sub_total_on_checkout
     */
    public function getSubTotalOnCheckoutAttribute($amount)
    {
        return $this->convertToMoney($this->unpackCurrency('BWP'), $amount);
    }

    /**
     *  Returns the sale_discount_total_on_checkout
     */
    public function getSaleDiscountTotalOnCheckoutAttribute($amount)
    {
        return $this->convertToMoney($this->unpackCurrency('BWP'), $amount);
    }

    /**
     *  Returns the coupon_total_on_checkout
     */
    public function getCouponTotalOnCheckoutAttribute($amount)
    {
        return $this->convertToMoney($this->unpackCurrency('BWP'), $amount);
    }

    /**
     *  Returns the coupon_and_sale_discount_total_on_checkout
     */
    public function getCouponAndSaleDiscountTotalOnCheckoutAttribute($amount)
    {
        return $this->convertToMoney($this->unpackCurrency('BWP'), $amount);
    }

    /**
     *  Returns the checkout_coupons_and_sale_discount_total
     */
    public function getDeliveryFeeOnCheckoutAttribute($amount)
    {
        return $this->convertToMoney($this->unpackCurrency('BWP'), $amount);
    }

    /**********************************
     *  CUSTOMER DATA ON CONVERSION   *
     *********************************/

    /**
     *  Returns the total_orders_placed_conversion
     */
    public function getTotalOrdersPlacedOnConversionAttribute($amount)
    {
        return $this->total_orders_placed_by_customer_on_conversion + $this->total_orders_placed_by_store_on_conversion;
    }

    /**
     *  Returns the grand_total_on_conversion
     */
    public function getGrandTotalOnConversionAttribute($amount)
    {
        return $this->convertToMoney($this->unpackCurrency('BWP'), $amount);
    }

    /**
     *  Returns the sub_total_on_conversion
     */
    public function getSubTotalOnConversionAttribute($amount)
    {
        return $this->convertToMoney($this->unpackCurrency('BWP'), $amount);
    }

    /**
     *  Returns the sale_discount_total_on_conversion
     */
    public function getSaleDiscountTotalOnConversionAttribute($amount)
    {
        return $this->convertToMoney($this->unpackCurrency('BWP'), $amount);
    }

    /**
     *  Returns the coupon_total_on_conversion
     */
    public function getCouponTotalOnConversionAttribute($amount)
    {
        return $this->convertToMoney($this->unpackCurrency('BWP'), $amount);
    }

    /**
     *  Returns the coupon_and_sale_discount_total_on_conversion
     */
    public function getCouponAndSaleDiscountTotalOnConversionAttribute($amount)
    {
        return $this->convertToMoney($this->unpackCurrency('BWP'), $amount);
    }

    /**
     *  Returns the checkout_coupons_and_sale_discount_total
     */
    public function getDeliveryFeeOnConversionAttribute($amount)
    {
        return $this->convertToMoney($this->unpackCurrency('BWP'), $amount);
    }
}
