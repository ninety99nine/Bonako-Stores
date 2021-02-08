<?php

namespace App;

use App\Traits\CommonTraits;
use App\Traits\DeliveryLineTraits;
use Illuminate\Database\Eloquent\Model;

class DeliveryLine extends Model
{
    use CommonTraits, DeliveryLineTraits;

    protected $with = ['addressType'];
    
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [

        /*  Basic Info  */
        'name', 'mobile_number', 'physical_address', 'address_type_id', 
        'delivery_type', 'day', 'time', 'destination', 

        /*  Address Info  */
        'address_id',

        /*  Ownership Information  */
        'order_id'

    ];

    /**
     *  Returns the order of this delivery line
     */
    public function order()
    {
        return $this->belongsTo('App\Order');
    }

    /**
     *  Returns the address linked to this delivery line
     */
    public function address()
    {
        return $this->belongsTo('App\Address');
    }

    /**
     *  Returns the address type
     */
    public function addressType()
    {
        return $this->belongsTo('App\AddressType');
    }

    /** ATTRIBUTES
     *
     *  Note that the "resource_type" is defined within CommonTraits
     *
     */
    protected $appends = [
        'resource_type',
    ];

    public function getDeliveryTypeAttribute($value)
    {
        //  If the value is "d" then convert to "delivery"
        if( $value == 'd' ){

            //  Return "delivery"
            return 'delivery';

        //  If the value is "p" then convert to "pickup"
        }elseif( $value == 'p' ){

            //  Return "pickup"
            return 'pickup';

        }
    }

    public function setDeliveryTypeAttribute($value)
    {   
        $first_letter = strtolower(substr($value, 0, 1));

        //  If the first letter is "d" or "p" which stands for "delivery" or "pickup"
        if( in_array($first_letter, ['d', 'p']) ){

            //  Set the value
            $this->attributes['delivery_type'] = $first_letter;

        }else{

            //  Set default value
            $this->attributes['delivery_type'] = 'd';

        }
    }

}
