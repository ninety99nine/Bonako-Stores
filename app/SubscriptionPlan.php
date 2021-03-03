<?php

namespace App;

use App\Traits\CommonTraits;
use App\Traits\SubscriptionPlanTraits;
use Illuminate\Database\Eloquent\Model;

class SubscriptionPlan extends Model
{
    use SubscriptionPlanTraits;
    use CommonTraits;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name', 'description', 'type', 'frequency', 'duration', 'currency', 'price'
    ];

    /*
     *  Returns the user that owns this transaction
     */
    public function owner()
    {
        return $this->belongsTo('App\User');
    }

    /** ATTRIBUTES
     *
     *  Note that the "resource_type" is defined within CommonTraits.
     */
    protected $appends = [
        'resource_type',
    ];

    /**
     *  Returns the subscription plan currency code and symbol
     */
    public function getCurrencyAttribute($currency_code)
    {
        return $this->unpackCurrency($currency_code);
    }

    /**
     *  Returns the subscription plan price
     */
    public function getPriceAttribute($amount)
    {
        return $this->convertToMoney($this->currency, $amount);
    }

    public function setCurrencyAttribute($value)
    {
        $this->attributes['currency'] = is_array($value) ? $value['code'] : $value;
    }

    public function setPriceAttribute($value)
    {
        $this->attributes['price'] = is_array($value) ? $value['price'] : $value;
    }

}
