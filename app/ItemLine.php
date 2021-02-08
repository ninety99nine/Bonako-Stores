<?php

namespace App;

use App\Traits\CommonTraits;
use App\Traits\ItemLineTraits;
use Illuminate\Database\Eloquent\Model;

class ItemLine extends Model
{
    use CommonTraits, ItemLineTraits;

    protected $with = ['product'];

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [

        /*  Basic Info  */
        'name', 'description',
        
        /*  Unit Pricing Info  */
        'unit_regular_price', 'unit_sale_price', 'unit_price', 'unit_sale_discount',
        
        /*  Total Pricing Info  */
        'sub_total', 'sale_discount_total', 'grand_total', 

        /*  Quantity Info  */
        'quantity',

        /*  Product Info  */
        'product_id', 

        /*  Ownership Information  */
        'cart_id'

    ];

    /**
     *  Returns the cart of this item line
     */
    public function cart()
    {
        return $this->belongsTo('App\Cart');
    }

    /**
     *  Returns the product of this item line
     */
    public function product()
    {
        return $this->belongsTo('App\Product');
    }

    /** ATTRIBUTES
     *
     *  Note that the "resource_type" is defined within CommonTraits
     *
     */
    protected $appends = [
        'resource_type',
    ];

}
