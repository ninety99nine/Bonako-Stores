<?php

namespace App;

use App\Traits\CommonTraits;
use App\Traits\CouponTraits;
use Illuminate\Database\Eloquent\Model;

class Coupon extends Model
{
    use CouponTraits, CommonTraits;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $casts = [

        'active' => 'boolean',
        'uses_code' => 'boolean',
        'always_apply' => 'boolean',
        'is_fixed_rate' => 'boolean',
        'is_percentage_rate' => 'boolean'

    ];

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name', 'description', 'active', 'always_apply', 'uses_code', 'code', 'currency', 'is_fixed_rate',
        'fixed_rate', 'is_percentage_rate', 'percentage_rate', 'location_id'
    ];

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
        'resource_type',
    ];

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

    public function setActiveAttribute($value)
    {
        $this->attributes['active'] = (($value == 'true' || $value == '1') ? 1 : 0);
    }

    public function setAlwaysApplyAttribute($value)
    {
        $this->attributes['always_apply'] = (($value == 'true' || $value == '1') ? 1 : 0);
    }

    public function setUsesCodeAttribute($value)
    {
        $this->attributes['uses_code'] = (($value == 'true' || $value == '1') ? 1 : 0);
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
