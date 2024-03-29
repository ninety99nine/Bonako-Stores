<?php

namespace App;

use App\Traits\CommonTraits;
use App\Traits\ReportTraits;
use Illuminate\Database\Eloquent\Model;

class Report extends Model
{
    use ReportTraits, CommonTraits;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $casts = [
        'metadata' => 'array',
    ];

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [

        /**
         *  type = What are we reporting?
         *      [Received, Cancelled, Removed, Delivery status updated, Payment status updated]
         *
         *  user_id = Who triggered the report?
         *      User with id = 1 (Brandon Tabona)
         *
         *  store_id = Which store is the report linked to?
         *      Store with id = 1 (Heavenly Fruits)
         *
         *  location_id = Which location is the report linked to?
         *      Location with id = 1 (Main Branch)
         *
         *  owner_id & owner_type = What resource is this report related to?
         *      Store, Location, User, Cart, Order, Transaction, Subscription, Product, Coupon, Instant Cart
         *
         *  metadata = What additional information is related to this report?
         *      { ... } object with addtional information e.g { Sub total, Coupon total, Grand total, e.t.c }
         */

        'type', 'metadata', 'user_id', 'store_id', 'location_id', 'owner_id', 'owner_type',

        /**
         *  We need to allow the created_at and updated_at fields to be fillable so that in
         *  special cases we can overide the timestamps when creating reports e.g when we
         *  create a cart abandonment report we need to overide the timestamp to reflect
         *  the time that the cart was actually abandoned instead of the current
         *  datetime.
         */
        'created_at', 'updated_at'

    ];

    /*
     *  Scope:
     *  Returns reports that are being searched
     */
    public function scopeSearch($query, $searchTerm)
    {
        return $query
                ->where('id', $searchTerm)
                ->orWhere('type', 'like', '%'.$searchTerm.'%');
    }

    /*
     *  Scope:
     *  Returns reports by start date
     */
    public function scopeStartDate($query, $date)
    {
        return $query->whereDate('created_at', '>=', $date);
    }

    /*
     *  Scope:
     *  Returns reports by end date
     */
    public function scopeEndDate($query, $date)
    {
        return $query->whereDate('created_at', '<=', $date);
    }

    /**
     * Get the owning resource e.g Store, Location, User, Cart, Order,
     * Transaction, Subscription, Product, Coupon, Instant Cart
     */
    public function owner()
    {
        return $this->morphTo();
    }

    /** ATTRIBUTES
     *
     *  Note that the "resource_type" is defined within CommonTraits
     *
     */
    protected $appends = [
        'resource_type'
    ];

}
