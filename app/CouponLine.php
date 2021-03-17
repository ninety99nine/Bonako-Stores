<?php

namespace App;

use App\Traits\CommonTraits;
use App\Traits\CouponLineTraits;
use Illuminate\Database\Eloquent\Model;

class CouponLine extends Model
{
    use CommonTraits, CouponLineTraits;

    protected $with = ['coupon'];

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [

        /*  Basic Info  */
        'name', 'description', 'always_apply', 'uses_code', 'code', 'allow_free_delivery', 'currency',
        'discount_rate_type', 'fixed_rate', 'percentage_rate', 'allow_discount_on_minimum_total',
        'discount_on_minimum_total', 'allow_discount_on_total_items', 'discount_on_total_items',
        'allow_discount_on_total_unique_items', 'discount_on_total_unique_items',

        /*  Coupon Info  */
        'coupon_id',

        /*  Ownership Information  */
        'cart_id'

    ];

    /**
     *  Returns the cart of this coupon line
     */
    public function cart()
    {
        return $this->belongsTo('App\Cart');
    }

    /**
     *  Returns the product of this coupon line
     */
    public function coupon()
    {
        return $this->belongsTo('App\Coupon');
    }

    /** ATTRIBUTES
     *
     *  Note that the "resource_type" is defined within CommonTraits
     *
     */
    protected $appends = [
        'resource_type',
    ];

    /**
     *  Returns the coupon always_apply status and description
     */
    public function getAlwaysApplyAttribute($value)
    {
        return [
            'status' => $value ? true : false,
            'name' => $value ? 'Yes' : 'No',
            'description' => $value ? 'Always use this coupon for every cart'
                                    : 'Only use this coupon when directly applied to a cart'
        ];
    }

    /**
     *  Returns the coupon uses_code status and description
     */
    public function getUsesCodeAttribute($value)
    {
        return [
            'status' => $value ? true : false,
            'name' => $value ? 'Yes' : 'No',
            'description' => $value ? 'Requires a coupon code to be applied to a cart'
                                    : 'Does not require a coupon code to be applied to a cart'
        ];
    }

    /**
     *  Returns the coupon allow_free_delivery status and description
     */
    public function getAllowFreeDeliveryAttribute($value)
    {
        return [
            'status' => $value ? true : false,
            'name' => $value ? 'Yes' : 'No',
            'description' => $value ? 'This coupon supports free delivery of orders'
                                    : 'This coupon does not support free delivery of orders'
        ];
    }

    /**
     *  Returns the coupon discount_rate_type status and description
     */
    public function getDiscountRateTypeAttribute($value)
    {
        return [
            'type' => $value == 'p' ? 'Percentage' : 'Fixed',
            'name' => $value == 'p' ? 'Percentage rate' : 'Fixed rate',
            'description' => $value == 'p' ? 'Discount by percentage rate'
                                           : 'Discount by fixed rate'
        ];
    }

    /**
     *  Returns the coupon allow_discount_on_minimum_total status and description
     */
    public function getAllowDiscountOnMinimumTotalAttribute($value)
    {
        return [
            'status' => $value ? true : false,
            'name' => $value ? 'Yes' : 'No',
            'description' => $value ? 'Discount only if the cart is equal or greater than then minimum total'
                                    : 'Discount for any cart total'
        ];
    }

    /**
     *  Returns the coupon allow_discount_on_minimum_total status and description
     */
    public function getAllowDiscountOnTotalItemsAttribute($value)
    {
        return [
            'status' => $value ? true : false,
            'name' => $value ? 'Yes' : 'No',
            'description' => $value ? 'Discount only if the cart total items is equal or greater than then minimum total items'
                                    : 'Discount for any cart total items'
        ];
    }

    /**
     *  Returns the coupon allow_discount_on_total_unique_items status and description
     */
    public function getAllowDiscountOnTotalUniqueItemsAttribute($value)
    {
        return [
            'status' => $value ? true : false,
            'name' => $value ? 'Yes' : 'No',
            'description' => $value ? 'Discount only if the cart total unique items is equal or greater than then minimum total unique items'
                                    : 'Discount for any cart total items'
        ];
    }

    /**
     *  Returns the coupon currency code and symbol
     */
    public function getCurrencyAttribute($currency_code)
    {
        return $this->unpackCurrency($currency_code);
    }

    /**
     *  Returns the coupon fixed rate
     */
    public function getFixedRateAttribute($amount)
    {
        return $this->convertToMoney($this->currency, $amount);
    }

    /**
     *  Returns the coupon discount_on_minimum_total
     */
    public function getDiscountOnMinimumTotalAttribute($amount)
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

    public function setAlwaysApplyAttribute($value)
    {
        if( is_array($value) ){
            $this->attributes['always_apply'] = (in_array($value['status'], ['true', true, '1', 1]) ? 1 : 0);
        }else{
            $this->attributes['always_apply'] = (($value == 'true' || $value == '1') ? 1 : 0);
        }
    }

    public function setUsesCodeAttribute($value)
    {
        if( is_array($value) ){
            $this->attributes['uses_code'] = (in_array($value['status'], ['true', true, '1', 1]) ? 1 : 0);
        }else{
            $this->attributes['uses_code'] = (($value == 'true' || $value == '1') ? 1 : 0);
        }
    }

    public function setAllowFreeDeliveryAttribute($value)
    {
        if( is_array($value) ){
            $this->attributes['allow_free_delivery'] = (in_array($value['status'], ['true', true, '1', 1]) ? 1 : 0);
        }else{
            $this->attributes['allow_free_delivery'] = (($value == 'true' || $value == '1') ? 1 : 0);
        }
    }

    public function setCurrencyAttribute($value)
    {
        $this->attributes['currency'] = is_array($value) ? $value['code'] : $value;
    }

    public function setFixedRateAttribute($value)
    {
        $this->attributes['fixed_rate'] = is_array($value) ? $value['amount'] : $value;
    }

    public function setDiscountOnMinimumTotalAttribute($value)
    {
        $this->attributes['discount_on_minimum_total'] = is_array($value) ? $value['amount'] : $value;
    }

    public function setDiscountRateTypeAttribute($value)
    {
        if( is_array($value) ){
            $this->attributes['discount_rate_type'] = substr(strtolower($value['name']), 0, 1) == 'p' ? 'p' : 'f';
        }else{
            $this->attributes['discount_rate_type'] = substr(strtolower($value), 0, 1) == 'p' ? 'p' : 'f';
        }
    }

    public function setAllowDiscountOnMinimumTotalAttribute($value)
    {
        if( is_array($value) ){
            $this->attributes['allow_discount_on_minimum_total'] = (in_array($value['status'], ['true', true, '1', 1]) ? 1 : 0);
        }else{
            $this->attributes['allow_discount_on_minimum_total'] = (($value == 'true' || $value == '1') ? 1 : 0);
        }
    }

    public function setAllowDiscountOnTotalItemsAttribute($value)
    {
        if( is_array($value) ){
            $this->attributes['allow_discount_on_total_items'] = (in_array($value['status'], ['true', true, '1', 1]) ? 1 : 0);
        }else{
            $this->attributes['allow_discount_on_total_items'] = (($value == 'true' || $value == '1') ? 1 : 0);
        }
    }

    public function setAllowDiscountOnTotalUniqueItemsAttribute($value)
    {
        if( is_array($value) ){
            $this->attributes['allow_discount_on_total_unique_items'] = (in_array($value['status'], ['true', true, '1', 1]) ? 1 : 0);
        }else{
            $this->attributes['allow_discount_on_total_unique_items'] = (($value == 'true' || $value == '1') ? 1 : 0);
        }
    }

}
