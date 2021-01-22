<?php

namespace App;

use Carbon\Carbon;
use App\Traits\CommonTraits;
use App\Traits\SubscriptionTraits;
use Illuminate\Database\Eloquent\Model;

class Subscription extends Model
{
    use SubscriptionTraits;
    use CommonTraits;

    /**
     * The attributes that should be mutated to dates.
     *
     * @var array
     */
    protected $dates = [
        'start_at',
        'end_at'
    ];

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'user_id', 'subscription_plan_id', 'store_id', 'transaction_id', 'start_at', 'end_at', 'active'
    ];

    /** ATTRIBUTES
     *
     *  Note that the "resource_type" is defined within CommonTraits.
     */
    protected $appends = [
        'resource_type',
    ];

    /*
     *  Scope:
     *  Returns subscriptions where user is the owner. This means
     *  that the subscriptions were created by the user.
     */
    public function scopeAsOwner($query, $user_id)
    {
        return $query->where('user_id', '=', $user_id);
    }

    /*
     *  Scope:
     *  Returns subscriptions that are active
     */
    public function scopeActive($query, $user_id)
    {
        return $query->where('end_at', '>', Carbon::now());
    }

    /*
     *  Scope:
     *  Returns subscriptions that are not active
     */
    public function scopeInactive($query, $user_id)
    {
        return $query->where('end_at', '<', Carbon::now());
    }

    /*
     *  Returns the user that owns this subscription
     */
    public function owner()
    {
        return $this->belongsTo('App\User');
    }

    /*
     *  Returns the subscription plan that is being subscribed to
     */
    public function subscriptionPlan()
    {
        return $this->belongsTo('App\SubscriptionPlan');
    }

    /*
     *  Returns the subscription transaction
     */
    public function transaction()
    {
        return $this->belongsTo('App\Transaction');
    }

    /*
     *  Returns the store covered by this subscription
     */
    public function store()
    {
        return $this->belongsTo('App\Store');
    }

    /**
     * Get the subscription payment short code
     */
    public function paymentShortCode()
    {
        return $this->morphOne(ShortCode::class, 'owner');
    }

    //  ON DELETE EVENT
    public static function boot()
    {
        try {
            parent::boot();

            // before delete() method call this
            static::deleting(function ($store) {

                //  Delete all transactions
                foreach ($store->transactions as $transaction) {
                    $transaction->delete();
                }

                // do the rest of the cleanup...
            });
        } catch (\Exception $e) {
            throw($e);
        }
    }
}
