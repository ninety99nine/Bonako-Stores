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

        'total_coupons_used_on_checkout', 'total_instant_carts_used_on_checkout',
        'total_adverts_used_on_checkout', 'total_orders_placed_by_customer', 'total_orders_placed_by_store',

        'checkout_grand_total', 'checkout_sub_total', 'checkout_coupons_total', 'checkout_sale_discount_total',
        'checkout_coupons_and_sale_discount_total', 'checkout_delivery_fee', 'total_free_delivery_on_checkout',
        'checkout_total_items', 'checkout_total_unique_items',

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
        return $query->where('total_orders_placed_by_customer', '>', '0');
    }

    /**
     *  Scope:
     *  Returns customers that have orders
     */
    public function scopeHasOrdersPlacedByStore($query)
    {
        return $query->where('total_orders_placed_by_store', '>', '0');
    }

    /**
     *  Scope:
     *  Returns customers that have used coupons
     */
    public function scopeHasUsedCoupons($query)
    {
        return $query->where('total_coupons_used', '>', '0');
    }

    /**
     *  Scope:
     *  Returns customers that have used instant carts
     */
    public function scopeHasUsedInstantCarts($query)
    {
        return $query->where('total_instant_carts_used', '>', '0');
    }

    /**
     *  Scope:
     *  Returns customers that have used adverts
     */
    public function scopeHasUsedAdverts($query)
    {
        return $query->where('total_adverts_used_on_checkout', '>', '0');
    }

    /**
     *  Scope:
     *  Returns customers that have used adverts
     */
    public function scopeHasOrdersWithFreeDelivery($query)
    {
        return $query->where('total_free_delivery_on_checkout', '>', '0');
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
        'resource_type'
    ];

    /**
     *  Returns the checkout_grand_total
     */
    public function getcheckoutGrandTotalAttribute($amount)
    {
        return $this->convertToMoney($this->unpackCurrency('BWP'), $amount);
    }

    /**
     *  Returns the checkout_sub_total
     */
    public function getCheckoutSubTotalAttribute($amount)
    {
        return $this->convertToMoney($this->unpackCurrency('BWP'), $amount);
    }

    /**
     *  Returns the checkout_coupons_total
     */
    public function getCheckoutCouponsTotalAttribute($amount)
    {
        return $this->convertToMoney($this->unpackCurrency('BWP'), $amount);
    }

    /**
     *  Returns the checkout_sale_discount_total
     */
    public function getCheckoutSaleDiscountTotalAttribute($amount)
    {
        return $this->convertToMoney($this->unpackCurrency('BWP'), $amount);
    }

    /**
     *  Returns the checkout_coupons_and_sale_discount_total
     */
    public function getCheckoutCouponsAndSaleDiscountTotalAttribute($amount)
    {
        return $this->convertToMoney($this->unpackCurrency('BWP'), $amount);
    }

    /**
     *  Returns the checkout_coupons_and_sale_discount_total
     */
    public function getCheckoutDeliveryFeeAttribute($amount)
    {
        return $this->convertToMoney($this->unpackCurrency('BWP'), $amount);
    }
}
