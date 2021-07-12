<?php

namespace App;

use App\Traits\CommonTraits;
use App\Traits\CouponTraits;
use Illuminate\Database\Eloquent\Model;

class Coupon extends Model
{
    use CouponTraits, CommonTraits;

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'discount_on_start_datetime' => 'datetime',
        'discount_on_end_datetime' => 'datetime',
    ];

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name', 'description', 'active', 'apply_discount', 'activation_type', 'code', 'allow_free_delivery',
        'currency', 'discount_rate_type', 'fixed_rate', 'percentage_rate', 'allow_discount_on_minimum_total',
        'discount_on_minimum_total', 'allow_discount_on_total_items', 'discount_on_total_items',
        'allow_discount_on_total_unique_items', 'discount_on_total_unique_items',
        'allow_discount_on_start_datetime', 'discount_on_start_datetime',
        'allow_discount_on_end_datetime', 'discount_on_end_datetime',
        'allow_usage_limit', 'usage_limit', 'usage_quantity',

        'location_id'
    ];

    /**
     *  Scope:
     *  Returns coupons that are being searched
     */
    public function scopeSearch($query, $searchTerm)
    {
        return $query->where('name', 'like', '%'.$searchTerm.'%');
    }

    /**
     *  Scope:
     *  Returns coupons that are active
     */
    public function scopeActive($query)
    {
        return $query->whereActive('1');
    }

    /**
     *  Scope:
     *  Returns coupons that are not active
     */
    public function scopeInActive($query)
    {
        return $query->whereActive('0');
    }

    /**
     *  Scope:
     *  Returns coupons that offer free delivery
     */
    public function scopeOffersFreeDelivery($query)
    {
        //  Free delivery
        return $query->where('allow_free_delivery', 1);
    }

    /*
     *  Returns the location of this coupon
     */
    public function location()
    {
        return $this->belongsTo('App\Location', 'location_id');
    }

    /**
     *  Returns the instant carts that this coupon is assigned to
     */
    public function instantCarts()
    {
        return $this->morphedByMany('App\InstantCart', 'owner');
    }

    /** ATTRIBUTES
     *
     *  Note that the "resource_type" is defined within CommonTraits
     *
     */
    protected $appends = [
        'resource_type', 'quantity_remaining', 'has_quantity_remaining'
    ];

    /**
     *  Returns the coupon active status and description
     */
    public function getActiveAttribute($value)
    {
        return [
            'status' => $value ? true : false,
            'name' => $value ? 'Active' : 'Inactive',
            'description' => $value ? 'This coupon is active'
                                    : 'This coupon is not active'
        ];
    }

    /**
     *  Returns the coupon apply_discount status and description
     */
    public function getApplyDiscountAttribute($value)
    {
        return [
            'status' => $value ? true : false,
            'name' => $value ? 'Yes' : 'No',
            'description' => $value ? 'Apply a discount using this coupon'
                                    : 'Do not apply a discount using this coupon'
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
     *  Returns the coupon activation_type status and description
     */
    public function getActivationTypeAttribute($value)
    {
        return [
            'type' => $value == '1' ? 'always apply' : 'use code',
            'name' => $value == '1' ? 'Always apply' : 'Use code',
            'description' => $value ? 'Always apply this coupon for every cart'
                                    : 'Only apply this coupon using a coupon code'
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
     *  Returns the coupon allow_discount_on_start_datetime status and description
     */
    public function getAllowDiscountOnStartDatetimeAttribute($value)
    {
        return [
            'status' => $value ? true : false,
            'name' => $value ? 'Yes' : 'No',
            'description' => $value ? 'Discount only if the start time has been reached'
                                    : 'Discount for any time'
        ];
    }

    /**
     *  Returns the coupon allow_discount_on_end_datetime status and description
     */
    public function getAllowDiscountOnEndDatetimeAttribute($value)
    {
        return [
            'status' => $value ? true : false,
            'name' => $value ? 'Yes' : 'No',
            'description' => $value ? 'Discount only if the end time has not been reached'
                                    : 'Discount for any time'
        ];
    }

    /**
     *  Returns the coupon allow_usage_limit status and description
     */
    public function getAllowUsageLimitAttribute($value)
    {
        return [
            'status' => $value ? true : false,
            'name' => $value ? 'Yes' : 'No',
            'description' => $value ? 'Discount only if the usage limit has not been exceeded'
                                    : 'Discount for regardless of usage'
        ];
    }

    /**
     *  Returns the coupon usage_limit as integer
     */
    public function getUsageLimitAttribute($amount)
    {
        return (int) $amount;
    }

    /**
     *  Returns the coupon usage_quantity as integer
     */
    public function getUsageQuantityAttribute($amount)
    {
        return (int) $amount;
    }

    /**
     *  Returns the coupon quantity_remaining
     */
    public function getQuantityRemainingAttribute()
    {
        /**
         *  usage_limit    = The maximum limit that cannot be exceeded
         *  usage_quantity = The total number of times this coupon has been used
         */
        return($this->usage_limit - $this->usage_quantity);
    }

    /**
     *  Returns true/false if the coupon has quantity
     */
    public function getHasQuantityRemainingAttribute()
    {
        //  If this product does not allow usage limit (Then it means we have unlimited quantity for use)
        $unlimited = $this->allow_usage_limit['status'] === false;

        if( $unlimited ){

            return [
                'status' => $unlimited,
                'name' => 'Unlimited',
                'description' => 'This coupon has unlimited use'
            ];

        }else{

            //  If this coupon allows usage limit and has usage limit
            $status = ($this->allow_usage_limit && $this->quantity_remaining);

            return [
                'status' => $status,
                'name' => $status ? 'Limited' : 'Finished',
                'description' => $status ? 'This coupon has limited use ('.$this->quantity_remaining.' left)'
                                         : 'This coupon has reached its limit of use'
            ];

        }

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
            $this->attributes['active'] = (in_array($value, ['true', true, '1', 1]) ? 1 : 0);
        }
    }

    public function setApplyDiscountAttribute($value)
    {
        if( is_array($value) ){
            $this->attributes['apply_discount'] = (in_array($value['status'], ['true', true, '1', 1]) ? 1 : 0);
        }else{
            $this->attributes['apply_discount'] = (in_array($value, ['true', true, '1', 1]) ? 1 : 0);
        }
    }

    public function setAllowFreeDeliveryAttribute($value)
    {
        if( is_array($value) ){
            $this->attributes['allow_free_delivery'] = (in_array($value['status'], ['true', true, '1', 1]) ? 1 : 0);
        }else{
            $this->attributes['allow_free_delivery'] = (in_array($value, ['true', true, '1', 1]) ? 1 : 0);
        }
    }

    public function setActivationTypeAttribute($value)
    {
        if( is_array($value) ){
            $this->attributes['activation_type'] = ((strtolower($value['type']) == 'always apply') ? 1 : 0);
        }else{
            $this->attributes['activation_type'] = ((strtolower($value) == 'always apply') ? 1 : 0);
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
            $this->attributes['discount_rate_type'] = substr(strtolower($value['name']), 0, 1) == 'p' ? 'p' : 'p';
        }else{
            $this->attributes['discount_rate_type'] = substr(strtolower($value), 0, 1) == 'p' ? 'p' : 'p';
        }
    }

    public function setAllowDiscountOnMinimumTotalAttribute($value)
    {
        if( is_array($value) ){
            $this->attributes['allow_discount_on_minimum_total'] = (in_array($value['status'], ['true', true, '1', 1]) ? 1 : 0);
        }else{
            $this->attributes['allow_discount_on_minimum_total'] = (in_array($value, ['true', true, '1', 1]) ? 1 : 0);
        }
    }

    public function setAllowDiscountOnTotalItemsAttribute($value)
    {
        if( is_array($value) ){
            $this->attributes['allow_discount_on_total_items'] = (in_array($value['status'], ['true', true, '1', 1]) ? 1 : 0);
        }else{
            $this->attributes['allow_discount_on_total_items'] = (in_array($value, ['true', true, '1', 1]) ? 1 : 0);
        }
    }

    public function setAllowDiscountOnTotalUniqueItemsAttribute($value)
    {
        if( is_array($value) ){
            $this->attributes['allow_discount_on_total_unique_items'] = (in_array($value['status'], ['true', true, '1', 1]) ? 1 : 0);
        }else{
            $this->attributes['allow_discount_on_total_unique_items'] = (in_array($value, ['true', true, '1', 1]) ? 1 : 0);
        }
    }

    public function setAllowDiscountOnStartDatetimeAttribute($value)
    {
        if( is_array($value) ){
            $this->attributes['allow_discount_on_start_datetime'] = (in_array($value['status'], ['true', true, '1', 1]) ? 1 : 0);
        }else{
            $this->attributes['allow_discount_on_start_datetime'] = (in_array($value, ['true', true, '1', 1]) ? 1 : 0);
        }
    }

    public function setAllowDiscountOnEndDatetimeAttribute($value)
    {
        if( is_array($value) ){
            $this->attributes['allow_discount_on_end_datetime'] = (in_array($value['status'], ['true', true, '1', 1]) ? 1 : 0);
        }else{
            $this->attributes['allow_discount_on_end_datetime'] = (in_array($value, ['true', true, '1', 1]) ? 1 : 0);
        }
    }

    public function setAllowUsageLimitAttribute($value)
    {
        if( is_array($value) ){
            $this->attributes['allow_usage_limit'] = (in_array($value['status'], ['true', true, '1', 1]) ? 1 : 0);
        }else{
            $this->attributes['allow_usage_limit'] = (in_array($value, ['true', true, '1', 1]) ? 1 : 0);
        }
    }

    public function setUsageLimitAttribute($value)
    {
        $this->attributes['usage_limit'] = ($value > 0) ? $value : 0;
    }

}
