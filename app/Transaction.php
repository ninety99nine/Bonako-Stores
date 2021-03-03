<?php

namespace App;

use App\Traits\CommonTraits;
use App\Traits\TransactionTraits;
use Illuminate\Database\Eloquent\Model;

class Transaction extends Model
{
    use TransactionTraits;
    use CommonTraits;

    protected $with = ['paymentMethod'];

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'number', 'type', 'currency', 'amount', 'payment_method_id', 'description', 'user_id', 'owner_id', 'owner_type'
    ];

    /*
     *  Returns the user that owns this transaction
     */
    public function owner()
    {
        return $this->belongsTo('App\User');
    }

    /*
     *  Returns the payment method used for this transaction
     */
    public function paymentMethod()
    {
        return $this->belongsTo('App\PaymentMethod');
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

}
