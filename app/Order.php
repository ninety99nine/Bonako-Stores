<?php

namespace App;

use App\Traits\CommonTraits;
use App\Traits\OrderTraits;
use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    use CommonTraits;
    use OrderTraits;

    protected $with = ['fulfillments'];

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
        'request_customer_rating_at',
    ];

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        /*  Basic Info  */
        'number', 'currency', 'created_date',

        /*  Rating Info  */
        'request_customer_rating_at',

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
        'customer_id', 'customer_info',

        /*  Delivery Info  */
        'delivery_info',

        /*  Checkout Info  */
        'checkout_method',

        /*  Store Info  */
        'store_id',

        /*  Location Info  */
        'location_id',
    ];

    protected $allowedFilters = [];

    protected $allowedOrderableColumns = [];

    /*
     *  Scope:
     *  Returns orders that are being searched
     */
    public function scopeSearch($query, $searchTerm)
    {
        return $query->where('number', $searchTerm);
    }

    /*
     *  Scope:
     *  Returns orders that are fulfilled
     */
    public function scopeFulfilled($query)
    {
        return $query->where('fulfillment_status', 'fulfilled')->open();
    }

    /*
     *  Scope:
     *  Returns orders that are unfulfilled
     */
    public function scopeUnfulfilled($query)
    {
        return $query->where('fulfillment_status', 'unfulfilled')->open();
    }

    /*
     *  Scope:
     *  Returns orders that are open
     */
    public function scopeOpen($query)
    {
        return $query->where('status', 'open');
    }

    /*
     *  Scope:
     *  Returns orders that are archieved
     */
    public function scopeArchieved($query)
    {
        return $query->where('status', 'archieved');
    }

    /*
     *  Scope:
     *  Returns orders that are cancelled
     */
    public function scopeCancelled($query)
    {
        return $query->where('status', 'cancelled');
    }

    /*
     *  Scope:
     *  Returns orders that require a rating
     */
    public function scopeRequireRating($query)
    {
        return $query->where('customer_id', auth('api')->user()->id)
                     ->where('request_customer_rating_at', '<=', \Carbon\Carbon::now());
    }

    /**
     *  Returns the owning store.
     */
    public function store()
    {
        return $this->belongsTo('App\Store');
    }

    /**
     *  Returns the the owning location.
     */
    public function location()
    {
        return $this->belongsTo('App\Location');
    }

    /*
     *  Returns the order fulfillments
     */
    public function fulfillments()
    {
        return $this->hasMany('App\Fulfillment')->latest();
    }

    /** ATTRIBUTES
     *
     *  Note that the "resource_type" is defined within CommonTraits.
     */
    protected $appends = [
        'resource_type', 'status', 'payment_status', 'fulfillment_status', 'unfulfilled_item_lines',
        'quantity_of_unfulfilled_item_lines', 'quantity_of_fulfilled_item_lines',
    ];

    /*
     *  Returns the order unfulfilled item lines
     */
    public function getUnfulfilledItemLinesAttribute()
    {
        try {
            $item_lines = $this->item_lines;

            $fulfillments = $this->fulfillments;

            $unfulfilled_item_lines = [];

            if ($item_lines) {
                //  Foreach order item line
                foreach ($item_lines as $item_line) {
                    //  Lets get the current order item line quantity value
                    $item_quantity = intval($item_line['quantity']);

                    //  Foreach fulfillment instance [Since we can have multiple fulfillment instances]
                    foreach ($fulfillments as $fulfillment) {
                        //  Foreach item line of the current fulfillment instance
                        foreach ($fulfillment->item_lines as $fulfillment_item_line) {
                            //  Lets check if the current fulfillment item line matches the current order item line
                            if ($fulfillment_item_line['id'] == $item_line['id']) {
                                //  Lets get the current fulfillment item line quantity value
                                $fulfillment_item_quantity = intval($fulfillment_item_line['quantity']);

                                /** Calculate if we have any remaining quantities of the matching item that are not yet fulfilled.
                                 *  Assumiing that:.
                                 *
                                 *  $item_quantity = 5 and
                                 *  $fulfillment_item_quantity = 2
                                 *
                                 *  This means that if we subtract $fulfillment_item_quantity (2) from $item_quantity (5) we
                                 *  will get the number of remaining unfulfilled items (3) for the same matching item.
                                 *
                                 *  $item_quantity (3) = $item_quantity (5) - $fulfillment_item_quantity (2)
                                 */
                                $item_quantity = $item_quantity - $fulfillment_item_quantity;
                            }
                        }
                    }

                    //  If we have any remaining quantities that haven't yet been fulfilled for this item line
                    if ($item_quantity > 0) {
                        //  Get the unfulfilled/partially fulfilled item line
                        $unfulfilled_item_line = $item_line;

                        //  Update the remaining quantities that require fulfillment for this item line
                        $unfulfilled_item_line['quantity'] = $item_quantity;

                        //  Push the unfulfilled item
                        array_push($unfulfilled_item_lines, $unfulfilled_item_line);
                    }
                }
            }

            return $unfulfilled_item_lines;
        } catch (\Exception $e) {
            throw($e);
        }
    }

    /*
     *  Returns the quantity unfulfilled item lines
     */
    public function getQuantityOfUnfulfilledItemLinesAttribute()
    {
        try {
            $quantity = 0;

            $unfulfilled_item_lines = $this->unfulfilled_item_lines;

            if ($unfulfilled_item_lines) {
                //  Foreach item line
                foreach ($unfulfilled_item_lines as $unfulfilled_item_line) {
                    //  Lets get the current fulfillment item line quantity value
                    $quantity = $quantity + intval($unfulfilled_item_line['quantity']);
                }
            }

            return $quantity;
        } catch (\Exception $e) {
            throw($e);
        }
    }

    /*
     *  Returns the quantity fulfilled item lines
     */
    public function getQuantityOfFulfilledItemLinesAttribute()
    {
        try {
            $quantity = 0;

            $fulfillments = $this->fulfillments;

            //  Foreach item line
            foreach ($fulfillments as $fulfillment) {
                //  Foreach item line of the current fulfillment instance
                foreach ($fulfillment->item_lines as $fulfillment_item_line) {
                    //  Lets get the current fulfillment item line quantity value
                    $quantity = $quantity + intval($fulfillment_item_line['quantity']);
                }
            }

            return $quantity;
        } catch (\Exception $e) {
            throw($e);
        }
    }

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
