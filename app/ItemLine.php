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
        'is_free', 'currency', 'unit_regular_price', 'unit_sale_price', 'unit_price', 'unit_sale_discount',

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

    /**
     *  Returns the product visibile status and description
     */
    public function getIsFreeAttribute($value)
    {
        return [
            'status' => $value ? true : false,
            'name' => $value ? 'Free' : 'Not Free',
            'description' => $value ? 'This product is free'
                                    : 'This product is not free'
        ];
    }

    public function setIsFreeAttribute($value)
    {
        if( is_array($value) ){
            $this->attributes['is_free'] = (in_array($value['status'], ['true', true, '1', 1]) ? 1 : 0);
        }else{
            $this->attributes['is_free'] = (($value == 'true' || $value == '1') ? 1 : 0);
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
