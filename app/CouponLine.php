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
        'discount_on_months_of_the_year' => 'array',

        'detected_changes' => 'array'
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
        'allow_usage_limit', 'quantity_remaining',

        'allow_discount_on_times', 'discount_on_times', 'allow_discount_on_days_of_the_week',
        'discount_on_days_of_the_week', 'allow_discount_on_days_of_the_month',
        'discount_on_days_of_the_month', 'allow_discount_on_months_of_the_year',
        'discount_on_months_of_the_year', 'allow_discount_on_new_customer',
        'allow_discount_on_existing_customer',

        /*  Change Info  */
        'is_cancelled', 'cancellation_reason', 'detected_changes',

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
            'description' => $value ? 'Offer a discount using this coupon'
                                    : 'Do not apply a discount using this coupon'
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
            'description' => $value == 'p' ? 'The customer order was discounted using a percentage rate of ' .$this->percentage_rate. '%'
                                           : 'The customer order was discounted using a fixed rate of ' .$this->fixed_rate['currency_money']
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
            'description' => $value ? 'Offer free delivery using this coupon'
                                    : 'This coupon does not offer free delivery'
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
            'description' => $value ? 'Always apply this coupon for every checkout (No code required)'
                                    : 'Only apply this coupon using a coupon code. In this case the customer applied the coupon code "' .$this->code.'" to qualify for this coupon'
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
            'description' => $value ? 'Activate coupon if the cart is equal or greater than the minimum total. In this case the customer placed items valued at '.$this->discount_on_minimum_total['currency_money'].' or more to qualify for this coupon.'
                                    : 'Activate coupon for any total'
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
            'description' => $value ? 'Activate coupon if the cart total items is equal or greater than the minimum total items. In this case the customer placed '.$this->discount_on_total_items.' or more items qualify for this coupon.'
                                    : 'Activate coupon for any total items'
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
            'description' => $value ? 'Activate coupon if the cart total unique items is equal or greater than the minimum total unique items. In this case the customer placed '.$this->discount_on_total_unique_items.' or more unique items qualify for this coupon.'
                                    : 'Activate coupon for any total unique items'
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
            'description' => $value ? 'Activate coupon if the start date has been reached. In this case the coupon was claimed on ' . \Carbon\Carbon::parse($this->created_at)->format('M d Y @ H:i').' which is after the start date'
                                    : 'Activate coupon for any date'
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
            'description' => $value ? 'Activate coupon if the end date has not been reached. In this case the coupon was claimed on ' . \Carbon\Carbon::parse($this->created_at)->format('M d Y @ H:i').' which is before the end date'
                                    : 'Activate coupon for any date'
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
            'description' => $value ? 'Activate coupon if the usage limit has not been exceeded. In this case the coupon was claimed while a total of '.($this->quantity_remaining . ($this->quantity_remaining == 1 ? ' coupon was' : ' coupons were')).' still available for grabs'
                                    : 'Activate coupon regardless of usage limits'
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
            'description' => $value ? 'Activate coupon on the following times of the day ('.$times_of_day.'). In this case the coupon was claimed at ' . \Carbon\Carbon::parse($this->created_at)->format('H:i').' on ' .\Carbon\Carbon::parse($this->created_at)->format('M d Y') . ', qualifying the coupon for activation'
                                    : 'Activate coupon on any time of the day'
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
            'description' => $value ? 'Activate coupon on the following days of the week ('.$days_of_the_week.'). In this case the coupon was claimed on ' . \Carbon\Carbon::parse($this->created_at)->format('l').' of ' .\Carbon\Carbon::parse($this->created_at)->format('M d Y') . ', qualifying the coupon for activation'
                                    : 'Activate coupon on any day of the week'
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
            'description' => $value ? 'Activate coupon on the following days of the month ('.$days_of_the_month.'). In this case the coupon was claimed on the ' . \Carbon\Carbon::parse($this->created_at)->format('jS').' of ' .\Carbon\Carbon::parse($this->created_at)->format('M Y') . ', qualifying the coupon for activation'
                                    : 'Activate coupon on any day of the month'
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
            'description' => $value ? 'Activate coupon on the following months of the year ('.$months_of_the_year.'). In this case the coupon was claimed on ' . \Carbon\Carbon::parse($this->created_at)->format('F d Y') . ', qualifying the coupon for activation'
                                    : 'Activate coupon on any month of the year'
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
            'description' => $value ? 'Activate coupon for new customers. In this case the coupon was claimed by a new customer, qualifying the coupon for activation'
                                    : 'Activate coupon regardless if this is not a new customer'
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
            'description' => $value ? 'Activate coupon for existing customers. In this case the coupon was claimed by an existing customer, qualifying the coupon for activation'
                                    : 'Activate coupon regardless if this is not an existing customer'
        ];
    }

    public function getDiscountOnTimesAttribute($value)
    {
        //  Convert to array
        return is_null($value) ? [] : json_decode($value, true);
    }

    public function getDiscountOnDaysOfTheWeekAttribute($value)
    {
        //  Convert to array
        return is_null($value) ? [] : json_decode($value, true);
    }

    public function getDiscountOnDaysOfTheMonthAttribute($value)
    {
        //  Convert to array
        return is_null($value) ? [] : json_decode($value, true);
    }

    public function getDiscountOnMonthsOfTheYearAttribute($value)
    {
        //  Convert to array
        return is_null($value) ? [] : json_decode($value, true);
    }

    /**
     *  Returns the coupon quantity_remaining as integer
     */
    public function getQuantityRemainingAttribute($amount)
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

    /**
     *  Returns the coupon line is cancelled status and description
     */
    public function getIsCancelledAttribute($value)
    {
        return [
            'status' => $value ? true : false,
            'name' => $value ? 'Cancelled' : 'Not Cancelled',
            'description' => $value ? 'This coupon is cancelled'
                                    : 'This coupon is not cancelled'
        ];
    }

    public function getDetectedChangesAttribute($value)
    {
        //  Convert to array
        return is_null($value) ? [] : json_decode($value, true);
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

    public function setQuantityRemainingAttribute($value)
    {
        $this->attributes['quantity_remaining'] = ($value > 0) ? $value : 0;
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

    public function setIsCancelledAttribute($value)
    {
        if( is_array($value) ){
            $this->attributes['is_cancelled'] = (in_array($value['status'], ['true', true, '1', 1]) ? 1 : 0);
        }else{
            $this->attributes['is_cancelled'] = (in_array($value, ['true', true, '1', 1]) ? 1 : 0);
        }
    }

}
