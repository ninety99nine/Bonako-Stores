<?php

namespace App;

use Carbon\Carbon;
use App\Traits\OrderTraits;
use App\Traits\CommonTraits;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\Relation;

class Order extends Model
{
    use CommonTraits, OrderTraits;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $casts = [
        'currency' => 'array',
        'item_lines' => 'array',
        'coupon_lines' => 'array',
        'customer_info' => 'array',
        'delivery_info' => 'array',
    ];

    /**
     * The attributes that should be mutated to dates.
     *
     * @var array
     */
    protected $dates = [
        'created_date',
    ];

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [

        /*  Basic Info  */
        'number', 'currency', 'created_date',

        /*  Status  */
        'status', 'payment_status', 'fulfillment_status',

        /*  Item Info  */
        'item_lines',

        /*  Coupon Info  */
        'coupon_lines',

        /*  Cart Info  */
        'sub_total', 'coupon_total', 'discount_total',  'coupon_and_discount_total', 
        'delivery_fee', 'grand_total',

        /*  Customer Info  */
        'customer_info',

        /*  Delivery Info  */
        'delivery_info',

        /*  Checkout Info  */
        'checkout_method',

        /*  Store Info  */
        'store_id',

        /*  Location Info  */
        'location_id'

    ];

    protected $allowedFilters = [];

    protected $allowedOrderableColumns = [];

    /**
     *  Returns the owning store
     */
    public function store()
    {
        return $this->belongsTo('App\Store');
    }

    /**
     *  Returns the the owning location
     */
    public function location()
    {
        return $this->belongsTo('App\Location');
    }

    /** ATTRIBUTES
     * 
     *  Note that the "resource_type" is defined within CommonTraits
     * 
     */
    protected $appends = [
        'resource_type', 'status', 'payment_status', 'fulfillment_status'
    ];



    /*
     *  Returns the current status name and description of the order
     */
    public function getStatusAttribute($value)
    {
        switch (ucwords($value)) {
            case 'Open':
                $status_description = 'The order is open for processing';
                break;
            case 'Archieved':
                $status_description = 'The order has been archieved';
                break;
            case 'Cancelled':
                $status_description = 'The order has been cancelled and no longer available for processing';
                break;
            case 'Draft':
                $status_description = 'The order is currently a draft and not yet available for processing.';
                break;
            default:
                $status_description = 'Status is unknown.';
        }

        return [
            'name' => ucwords($value),
            'description' => $status_description,
        ];
    }

    /*
     *  Returns the current payment status name and description of the order
     */
    public function getPaymentStatusAttribute($value)
    {
        switch (ucwords($value)) {
            case 'Paid':
                $status_description = 'The order has been paid';
                break;
            case 'Partially Paid':
                $status_description = 'The order has been partially paid';
                break;
            case 'Unpaid':
                $status_description = 'The order has not been paid';
                break;
            case 'Refunded':
                $status_description = 'The order has been refunded';
                break;
            case 'Failed Payment':
                $status_description = 'The order payment failed or was declined (unpaid)';
                break;
            default:
                $status_description = 'Status is unknown';
        }

        return [
            'name' => ucwords($value),
            'description' => $status_description,
        ];
    }

    /*
     *  Returns the current fulfillmnt status name and description of the order
     */
    public function getFulfillmentStatusAttribute($value)
    {
        switch (ucwords($value)) {
            case 'Unfulfilled':
                $status_description = 'The order is still awaiting fulfillment';
                break;
            case 'Partially Fulfilled':
                $status_description = 'The order has been partially fulfilled';
                break;
            case 'Fulfilled':
                $status_description = 'The order has been fulfilled';
                break;
            default:
                $status_description = 'Status is unknown';
        }

        return [
            'name' => ucwords($value),
            'description' => $status_description,
        ];
    }

}
