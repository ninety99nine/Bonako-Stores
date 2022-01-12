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
     * The table associated with the model.
     *
     * @var string
     */
    protected $casts = [
        'detected_changes' => 'array'
    ];

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [

        /*  Basic Info  */
        'name', 'description',

        /*  Tracking Info  */
        'sku', 'barcode',

        'is_free', 'is_cancelled', 'cancellation_reason', 'detected_changes',

        /*  Unit Pricing Info  */
        'currency', 'unit_regular_price', 'unit_sale_price', 'unit_price', 'unit_sale_discount',

        /*  Total Pricing Info  */
        'sub_total', 'sale_discount_total', 'grand_total',

        /*  Quantity Info  */
        'quantity', 'original_quantity',

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

    /**
     *  Returns the item line is free status and description
     */
    public function getIsFreeAttribute($value)
    {
        return [
            'status' => $value ? true : false,
            'name' => $value ? 'Free' : 'Not Free',
            'description' => $value ? 'This item is free'
                                    : 'This item is not free'
        ];
    }

    /**
     *  Returns the item line is cancelled status and description
     */
    public function getIsCancelledAttribute($value)
    {
        return [
            'status' => $value ? true : false,
            'name' => $value ? 'Cancelled' : 'Not Cancelled',
            'description' => $value ? 'This item is cancelled'
                                    : 'This item is not cancelled'
        ];
    }

    /**
     *  Returns true if the item lien is on sale
     */
    public function getOnSaleAttribute()
    {
        //  If we have a regular price and the sale price and if the sale price is less than the regular price
        $value = ( !$this->is_free['status'] && ($this->unit_sale_price['amount'] != 0) && ($this->unit_regular_price['amount'] != 0) &&
                 ( $this->unit_sale_price['amount'] < $this->unit_regular_price['amount'] ));

        return [
            'status' => $value,
            'name' => $value ? 'Sale' : 'No Sale',
            'description' => $value ? 'This item is on sale'
                                    : 'This item is not on sale'
        ];
    }

    /**
     *  Returns the item line currency code and symbol
     */
    public function getCurrencyAttribute($currency_code)
    {
        return $this->unpackCurrency($currency_code);
    }

    /**
     *  Returns the unit regular price
     */
    public function getUnitRegularPriceAttribute($amount)
    {
        return $this->convertToMoney($this->currency, $amount);
    }

    /**
     *  Returns the unit sale price
     */
    public function getUnitSalePriceAttribute($amount)
    {
        return $this->convertToMoney($this->currency, $amount);
    }

    /**
     *  Returns the unit price
     */
    public function getUnitPriceAttribute($amount)
    {
        return $this->convertToMoney($this->currency, $amount);
    }

    /**
     *  Returns the unit sale discount
     */
    public function getUnitSaleDiscountAttribute($amount)
    {
        return $this->convertToMoney($this->currency, $amount);
    }

    /**
     *  Returns the sub total
     */
    public function getSubTotalAttribute($amount)
    {
        return $this->convertToMoney($this->currency, $amount);
    }

    /**
     *  Returns the sale discount total
     */
    public function getSaleDiscountTotalAttribute($amount)
    {
        return $this->convertToMoney($this->currency, $amount);
    }

    /**
     *  Returns the grand total
     */
    public function getGrandTotalAttribute($amount)
    {
        return $this->convertToMoney($this->currency, $amount);
    }

    public function getDetectedChangesAttribute($value)
    {
        //  Convert to array
        return is_null($value) ? [] : json_decode($value, true);
    }

    public function setIsFreeAttribute($value)
    {
        if( is_array($value) ){
            $this->attributes['is_free'] = (in_array($value['status'], ['true', true, '1', 1]) ? 1 : 0);
        }else{
            $this->attributes['is_free'] = (in_array($value, ['true', true, '1', 1]) ? 1 : 0);
        }
    }

    public function setIsCancelledAttribute($value)
    {
        if( is_array($value) ){
            $this->attributes['is_cancelled'] = (in_array($value['status'], ['true', true, '1', 1]) ? 1 : 0);
        }else{
            $this->attributes['is_cancelled'] = (in_array($value, ['true', true, '1', 1]) ? 1 : 0);
        }
    }

    public function setCurrencyAttribute($value)
    {
        $this->attributes['currency'] = is_array($value) ? $value['code'] : $value;
    }

    public function setUnitRegularPriceAttribute($value)
    {
        $this->attributes['unit_regular_price'] = is_array($value) ? $value['amount'] : $value;
    }

    public function setUnitSalePriceAttribute($value)
    {
        $this->attributes['unit_sale_price'] = is_array($value) ? $value['amount'] : $value;
    }

    public function setUnitPriceAttribute($value)
    {
        $this->attributes['unit_price'] = is_array($value) ? $value['amount'] : $value;
    }

    public function setUnitSaleDiscountAttribute($value)
    {
        $this->attributes['unit_sale_discount'] = is_array($value) ? $value['amount'] : $value;
    }

    public function setSubTotalAttribute($value)
    {
        $this->attributes['sub_total'] = is_array($value) ? $value['amount'] : $value;
    }

    public function setSaleDiscountTotalAttribute($value)
    {
        $this->attributes['sale_discount_total'] = is_array($value) ? $value['amount'] : $value;
    }

    public function setGrandTotalAttribute($value)
    {
        $this->attributes['grand_total'] = is_array($value) ? $value['amount'] : $value;
    }

}
