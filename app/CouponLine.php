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
     * The table associated with the model.
     *
     * @var string
     */
    protected $casts = [

        'is_fixed_rate' => 'boolean',
        'is_percentage_rate' => 'boolean'

    ];

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [

        /*  Basic Info  */
        'name', 'description', 'code', 'currency', 'is_fixed_rate', 'fixed_rate', 
        'is_percentage_rate', 'percentage_rate', 

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
     *  Returns the coupon line currency code and symbol
     */
    public function getCurrencyAttribute($currency_code)
    {
        return $this->unpackCurrency($currency_code);
    }

    /**
     *  Returns the coupon line fixed rate
     */
    public function getFixedRateAttribute($amount)
    {
        return $this->convertToMoney($this->currency, $amount);
    }

    public function setCurrencyAttribute($value)
    {
        $this->attributes['currency'] = is_array($value) ? $value['code'] : $value;
    }

    public function setFixedRateAttribute($value)
    {
        $this->attributes['fixed_rate'] = is_array($value) ? $value['amount'] : $value;
    }

    public function setIsFixedRateAttribute($value)
    {
        $this->attributes['is_fixed_rate'] = (($value == 'true' || $value == '1') ? 1 : 0);
    }

    public function setIsPercentageRateAttribute($value)
    {
        $this->attributes['is_percentage_rate'] = (($value == 'true' || $value == '1') ? 1 : 0);
    }

}
