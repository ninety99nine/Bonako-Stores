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
        'name', 'description', 'code', 'is_fixed_rate', 'fixed_rate', 
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

    public function setIsFixedRateAttribute($value)
    {
        $this->attributes['is_fixed_rate'] = (($value == 'true' || $value == '1') ? 1 : 0);
    }

    public function setIsPercentageRateAttribute($value)
    {
        $this->attributes['is_percentage_rate'] = (($value == 'true' || $value == '1') ? 1 : 0);
    }

}