<?php

namespace App;

use Carbon\Carbon;
use App\Traits\CommonTraits;
use App\Traits\TransactionTraits;
use Illuminate\Database\Eloquent\Model;

class Transaction extends Model
{
    use TransactionTraits;
    use CommonTraits;

    protected $with = ['status', 'paymentMethod', 'paymentShortCode', 'user', 'payer'];

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [

        /*  Transaction Details  */
        'number', 'status_id', 'type', 'currency',

        /*  Amount Information  */
        'percentage_rate', 'amount',

        'payment_method_id', 'description',

        /*  Payer Information  */
        'payer_id',

        /*  Auth User Information  */
        'user_id',

        /*  Owenership Details  */
        'owner_id', 'owner_type'
    ];

    /**
     * Get the owning resource e.g Subscription, Order
     */
    public function owner()
    {
        return $this->morphTo();
    }

    /*
     *  Returns the authenticated user that initiated this transaction
     */
    public function user()
    {
        return $this->belongsTo('App\User');
    }

    /*
     *  Returns the user responsible to pay for this transaction
     */
    public function payer()
    {
        return $this->belongsTo('App\User', 'payer_id');
    }

    /**
     *  Returns the order status
     */
    public function status()
    {
        return $this->belongsTo('App\Status');
    }

    /*
     *  Returns the payment method used for this transaction
     */
    public function paymentMethod()
    {
        return $this->belongsTo('App\PaymentMethod');
    }

    /**
     *  Returns the short codes owned by this order
     *  Only short codes that are not expired are
     *  valid.
     */
    public function shortCodes()
    {
        return $this->morphMany(ShortCode::class, 'owner')->where('expires_at', '>', Carbon::now())->latest();
    }

    /**
     *  Returns the short code owned by this order
     *  Only short codes that are not expired are
     *  valid.
     */
    public function shortCode()
    {
        return $this->morphOne(ShortCode::class, 'owner')->where('expires_at', '>', Carbon::now())->latest();
    }

    /**
     *  Returns the payment short codes owned by this order
     */
    public function paymentShortCode()
    {
        return $this->shortCode()->where('action', 'payment');
    }


    /** ATTRIBUTES
     *
     *  Note that the "resource_type" is defined within CommonTraits.
     */
    protected $appends = [
        'resource_type',
    ];

    /**
     *  This method returns the transaction type capitalized
     */
    public function getTypeAttribute($value)
    {
        try {

            return ucwords($value);

        } catch (\Exception $e) {

            throw($e);

        }
    }

    /**
     *  Returns the transaction currency code and symbol
     */
    public function getCurrencyAttribute($currency_code)
    {
        return $this->unpackCurrency($currency_code);
    }

    /**
     *  Returns the transaction amount
     */
    public function getAmountAttribute($amount)
    {
        return $this->convertToMoney($this->currency, $amount);
    }

    public function setCurrencyAttribute($value)
    {
        $this->attributes['currency'] = is_array($value) ? $value['code'] : $value;
    }

    public function setAmountAttribute($value)
    {
        $this->attributes['amount'] = is_array($value) ? $value['amount'] : $value;
    }

}
