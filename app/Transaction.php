<?php

namespace App;

use App\Traits\CommonTraits;
use App\Traits\TransactionTraits;
use Illuminate\Database\Eloquent\Model;

class Transaction extends Model
{
    use TransactionTraits;
    use CommonTraits;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'number', 'type', 'amount', 'payment_method_id', 'description', 'user_id', 'owner_id', 'owner_type'
    ];

    /** ATTRIBUTES
     *
     *  Note that the "resource_type" is defined within CommonTraits.
     */
    protected $appends = [
        'resource_type',
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

}
