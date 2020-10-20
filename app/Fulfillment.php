<?php

namespace App;

use App\Traits\CommonTraits;
use Illuminate\Database\Eloquent\Model;

class Fulfillment extends Model
{
    use CommonTraits;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $casts = [
        'item_lines' => 'array',
        'recipient_info' => 'array',
    ];

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [

        /*  Fulfillment Details  */
        'notes', 
        
        /*  Item Info  */
        'item_lines', 
        
        /*  Recipient Info  */
        'recipient_info',

        /*  Ownership Information  */
        'order_id',

    ];

    protected $allowedFilters = [];

    protected $allowedOrderableColumns = [];


    /**
     *  Returns the owning order
     */
    public function order()
    {
        return $this->belongsTo('App\Order');
    }
    
    /* ATTRIBUTES */

    protected $appends = [
        'resource_type'
    ];

}
