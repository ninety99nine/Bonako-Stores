<?php

namespace App;

use App\Traits\CartTraits;
use App\Traits\CommonTraits;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;

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
        'allow_free_delivery', 'delivery_fee', 'grand_total', 'total_items', 'total_unique_items',

        /*  Currency Info  */
        'currency',

        /*  Location Info  */
        'location_id',

        /*  Ownership Information  */
        'owner_id', 'owner_type'

    ];

    /*
     *  Scope:
     *  Returns carts that are active (Visible carts)
     */
    public function scopeActive($query)
    {
        return $query->whereActive('1');
    }

    /*
     *  Scope:
     *  Returns carts that are not active (Hidden carts)
     */
    public function scopeInActive($query)
    {
        return $query->whereActive('0');
    }

    /**
     *  Scope:
     *  Returns instant carts that offer free delivery
     *  Free delivery is available if the cart offers
     *  it directly or indirectly via its coupon lines
     */
    public function scopeOffersFreeDelivery($query)
    {
        //  Free delivery
        return $query->where('allow_free_delivery', 1)->orWhereHas('couponLines', function (Builder $query) {
            $query->where('allow_free_delivery', 1);
        });
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
     *  Returns the cart active status and description
     */
    public function getActiveAttribute($value)
    {
        return [
            'status' => $value ? true : false,
            'name' => $value ? 'Active' : 'Inactive',
            'description' => $value ? 'This cart is active'
                                    : 'This cart is not active'
        ];
    }

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
     *  Returns the coupon allow_free_delivery status and description
     */
    public function getAllowFreeDeliveryAttribute($value)
    {
        return [
            'status' => $value ? true : false,
            'name' => $value ? 'Yes' : 'No',
            'description' => $value ? 'This cart supports free delivery of orders'
                                    : 'This cart does not support free delivery of orders'
        ];
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

    public function setActiveAttribute($value)
    {
        if( is_array($value) ){
            $this->attributes['active'] = (in_array($value['status'], ['true', true, '1', 1]) ? 1 : 0);
        }else{
            $this->attributes['active'] = (($value == 'true' || $value == '1') ? 1 : 0);
        }
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

    public function setAllowFreeDeliveryAttribute($value)
    {
        if( is_array($value) ){
            $this->attributes['allow_free_delivery'] = (in_array($value['status'], ['true', true, '1', 1]) ? 1 : 0);
        }else{
            $this->attributes['allow_free_delivery'] = (($value == 'true' || $value == '1') ? 1 : 0);
        }
    }

    public function setDeliveryFeeAttribute($value)
    {
        $this->attributes['delivery_fee'] = is_array($value) ? $value['amount'] : $value;
    }

    public function setGrandTotalAttribute($value)
    {
        $this->attributes['grand_total'] = is_array($value) ? $value['amount'] : $value;
    }

    //  ON DELETE EVENT
    public static function boot()
    {
        try {
            parent::boot();

            // before delete() method call this
            static::deleting(function ($cart) {

                //  Delete all item lines
                $cart->itemLines()->delete();

                //  Delete all coupon lines
                $cart->couponLines()->delete();

                // do the rest of the cleanup...
            });

        } catch (\Exception $e) {
            throw($e);
        }
    }
}
