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
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'discount_on_start_datetime' => 'datetime',
        'discount_on_end_datetime' => 'datetime',
        'discount_on_times' => 'array',
        'discount_on_days_of_the_week' => 'array',
        'discount_on_days_of_the_month' => 'array',
        'discount_on_months_of_the_year' => 'array'
    ];

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [

        /*  Basic Info  */
        'name', 'description', 'apply_discount', 'activation_type', 'code', 'allow_free_delivery', 'currency',
        'discount_rate_type', 'fixed_rate', 'percentage_rate', 'allow_discount_on_minimum_total',
        'discount_on_minimum_total', 'allow_discount_on_total_items', 'discount_on_total_items',
        'allow_discount_on_total_unique_items', 'discount_on_total_unique_items',
        'allow_discount_on_start_datetime', 'discount_on_start_datetime',
        'allow_discount_on_end_datetime', 'discount_on_end_datetime',
        'allow_usage_limit', 'usage_limit',

        'allow_discount_on_times', 'discount_on_times', 'allow_discount_on_days_of_the_week',
        'discount_on_days_of_the_week', 'allow_discount_on_days_of_the_month',
        'discount_on_days_of_the_month', 'allow_discount_on_months_of_the_year',
        'discount_on_months_of_the_year', 'allow_discount_on_new_customer',
        'allow_discount_on_existing_customer',

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
     *  Returns the coupon allow_discount_on_times status and description
     */
    public function getAllowDiscountOnTimesAttribute($value)
    {
        $times_of_day = collect($this->discount_on_times)->map(function($time){
            return (($time < 10) ? '0'.$time : $time).':00';
        })->join(', ', ' and ');

        return [
            'status' => $value ? true : false,
            'name' => $value ? 'Yes' : 'No',
            'description' => $value ? 'Allows discount only on the following times of the day ('.$times_of_day.')'
                                    : 'Discount on any time of the day'
        ];
    }

    /**
     *  Returns the coupon allow_discount_on_days_of_the_week status and description
     */
    public function getAllowDiscountOnDaysOfTheWeekAttribute($value)
    {
        $days_of_the_week = collect($this->discount_on_days_of_the_week)->join(', ', ' and ');

        return [
            'status' => $value ? true : false,
            'name' => $value ? 'Yes' : 'No',
            'description' => $value ? 'Allows discount only on the following days of the week ('.$days_of_the_week.')'
                                    : 'Discount on any day of the week'
        ];
    }

    /**
     *  Returns the coupon allow_discount_on_days_of_the_month status and description
     */
    public function getAllowDiscountOnDaysOfTheMonthAttribute($value)
    {
        $days_of_the_month = collect($this->discount_on_days_of_the_month)->map(function($day){
            return ($day < 10) ? '0'.$day : $day;
        })->join(', ', ' and ');

        return [
            'status' => $value ? true : false,
            'name' => $value ? 'Yes' : 'No',
            'description' => $value ? 'Allows discount only on the following days of the month ('.$days_of_the_month.')'
                                    : 'Discount on any day of the month'
        ];
    }

    /**
     *  Returns the coupon allow_discount_on_months_of_the_year status and description
     */
    public function getAllowDiscountOnMonthsOfTheYearAttribute($value)
    {
        $months_of_the_year = collect($this->discount_on_months_of_the_year)->join(', ', ' and ');

        return [
            'status' => $value ? true : false,
            'name' => $value ? 'Yes' : 'No',
            'description' => $value ? 'Allows discount only on the following months of the year ('.$months_of_the_year.')'
                                    : 'Discount on any month of the year'
        ];
    }

    /**
     *  Returns the coupon allow_discount_on_new_customer status and description
     */
    public function getAllowDiscountOnNewCustomerAttribute($value)
    {
        return [
            'status' => $value ? true : false,
            'name' => $value ? 'Yes' : 'No',
            'description' => $value ? 'Allows discount only for new customers'
                                    : 'Discount regardless if this is not a new customer'
        ];
    }

    /**
     *  Returns the coupon allow_discount_on_existing_customer status and description
     */
    public function getAllowDiscountOnExistingCustomerAttribute($value)
    {
        return [
            'status' => $value ? true : false,
            'name' => $value ? 'Yes' : 'No',
            'description' => $value ? 'Allows discount only for existing customers'
                                    : 'Discount regardless if this is not an existing customer'
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

    public function setActivationTypeAttribute($value)
    {
        if( is_array($value) ){
            $this->attributes['activation_type'] = ((strtolower($value['type']) == 'always apply') ? 1 : 0);
        }else{
            $this->attributes['activation_type'] = ((strtolower($value) == 'always apply') ? 1 : 0);
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








    public function setAllowDiscountOnTimesAttribute($value)
    {
        if( is_array($value) ){
            $this->attributes['allow_discount_on_times'] = (in_array($value['status'], ['true', true, '1', 1]) ? 1 : 0);
        }else{
            $this->attributes['allow_discount_on_times'] = (in_array($value, ['true', true, '1', 1]) ? 1 : 0);
        }
    }

    public function setAllowDiscountOnDaysOfTheWeekAttribute($value)
    {
        if( is_array($value) ){
            $this->attributes['allow_discount_on_days_of_the_week'] = (in_array($value['status'], ['true', true, '1', 1]) ? 1 : 0);
        }else{
            $this->attributes['allow_discount_on_days_of_the_week'] = (in_array($value, ['true', true, '1', 1]) ? 1 : 0);
        }
    }

    public function setAllowDiscountOnDaysOfTheMonthAttribute($value)
    {
        if( is_array($value) ){
            $this->attributes['allow_discount_on_days_of_the_month'] = (in_array($value['status'], ['true', true, '1', 1]) ? 1 : 0);
        }else{
            $this->attributes['allow_discount_on_days_of_the_month'] = (in_array($value, ['true', true, '1', 1]) ? 1 : 0);
        }
    }

    public function setAllowDiscountOnMonthsOfTheYearAttribute($value)
    {
        if( is_array($value) ){
            $this->attributes['allow_discount_on_months_of_the_year'] = (in_array($value['status'], ['true', true, '1', 1]) ? 1 : 0);
        }else{
            $this->attributes['allow_discount_on_months_of_the_year'] = (in_array($value, ['true', true, '1', 1]) ? 1 : 0);
        }
    }

    public function setAllowDiscountOnNewCustomerAttribute($value)
    {
        if( is_array($value) ){
            $this->attributes['allow_discount_on_new_customer'] = (in_array($value['status'], ['true', true, '1', 1]) ? 1 : 0);
        }else{
            $this->attributes['allow_discount_on_new_customer'] = (in_array($value, ['true', true, '1', 1]) ? 1 : 0);
        }
    }

    public function setAllowDiscountOnExistingCustomerAttribute($value)
    {
        if( is_array($value) ){
            $this->attributes['allow_discount_on_existing_customer'] = (in_array($value['status'], ['true', true, '1', 1]) ? 1 : 0);
        }else{
            $this->attributes['allow_discount_on_existing_customer'] = (in_array($value, ['true', true, '1', 1]) ? 1 : 0);
        }
    }

}
