<?php

namespace App;

use DB;
use App\Traits\CommonTraits;
use App\Traits\ProductTraits;
use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    use ProductTraits;
    use CommonTraits;

    protected $with = ['variables'];

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $casts = [
        'variant_attributes' => 'array',
    ];

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [

        /*  Product Management  */
        'name', 'description', 'show_description', 'sku', 'barcode', 'visible', 'product_type_id',

        /*  Variation Management  */
        'allow_variants', 'variant_attributes',

        /*  Pricing Management  */

        /*  Currency Info  */
        'is_free', 'currency', 'unit_regular_price', 'unit_sale_price', 'unit_cost',

        /*  Quantity Management  */
        'allow_multiple_quantity_per_order', 'allow_maximum_quantity_per_order',
        'maximum_quantity_per_order',

        /*  Stock Management  */
        'allow_stock_management', 'auto_manage_stock', 'stock_quantity',

        /*  Ownership Management  */
        'parent_product_id', 'user_id',

    ];


    /**
     *  Scope:
     *  Returns products that are not variables of another product
     */
    public function scopeIsNotVariation($query)
    {
        return $query->whereNull('parent_product_id');
    }

    /**
     *  Scope:
     *  Returns products that are being searched
     */
    public function scopeSearch($query, $searchTerm)
    {
        return $query->where('name', 'like', '%'.$searchTerm.'%')->orWhere('description', 'like', '%'.$searchTerm.'%');
    }

    /**
     *  Scope:
     *  Returns products that are visible (Visible products)
     */
    public function scopeVisible($query)
    {
        return $query->whereVisible('1');
    }

    /**
     *  Scope:
     *  Returns products that are not active (Hidden products)
     */
    public function scopeInVisible($query)
    {
        return $query->whereVisible('0');
    }

    /**
     *  Scope:
     *  Returns products that are on sale
     */
    public function scopeOnSale($query)
    {
        /**
         *  A product is on sale if we have the sale price or
         *  the sale price is less than the regular price
         */
        return $query->whereNotNull('unit_sale_price')
                        ->orWhere('unit_sale_price', '!=', '0')
                            ->orWhereRaw('unit_sale_price < unit_regular_price');
    }

    /*
     *  Scope:
     *  Returns products that are on sale
     */
    public function scopeNotOnSale($query)
    {
        /**
         *  A product is not on sale if we don't have the sale price or
         *  the sale price is more than or equal to the regular price
         */
        return $query->whereNull('unit_sale_price')
                        ->orWhere('unit_sale_price', '0')
                            ->orWhereRaw('unit_sale_price >= unit_regular_price');
    }

    /**
     *  Returns the owning store.
     */
    public function store()
    {
        return $this->belongsTo('App\Store');
    }

    /**
     *  Returns the associated locations
     */
    public function locations()
    {
        return $this->morphedByMany('App\Location', 'owner', 'product_allocations')->withTimestamps();
    }

    /**
     *  Returns the currency
     */
    public function currency()
    {
        return $this->belongsTo('App\Currency');
    }

    /**
     *  Returns the product variations. Variations are different versions
     *  of this product such as when this product is available in
     *  different sizes, colors or materials, then it will have
     *  variations with different variables.
     */
    public function variations()
    {
        return $this->hasMany('App\Product', 'parent_product_id');
    }

    /**
     *  Returns the product variables. These are properties that
     *  make this product a variation e.g size=small, color=blue,
     *  and material=cotton are all variables that make this
     *  product different from all other variable products.
     */
    public function variables()
    {
        return $this->hasMany('App\Variable', 'product_id');
    }

    /** ATTRIBUTES
     *
     *  Note that the "resource_type" is defined within CommonTraits.
     */
    protected $appends = [
        'resource_type', 'unit_price', 'on_sale', 'unit_sale_percentage', 'unit_sale_discount',
        'unit_profit', 'unit_loss', 'has_price', 'has_stock'
    ];

    /**
     *  Returns the product visibile status and description
     */
    public function getVisibleAttribute($value)
    {
        return [
            'status' => $value ? true : false,
            'name' => $value ? 'Visible' : 'Hidden',
            'description' => $value ? 'This product is visible from the store and can be selected by customers'
                                    : 'This product is hidden from the store and cannot be selected by customers'
        ];
    }

    /**
     *  Returns the product show description status and description
     */
    public function getShowDescriptionAttribute($value)
    {
        return [
            'status' => $value ? true : false,
            'name' => $value ? 'Show' : 'Hide',
            'description' => $value ? 'This product description will be shown to the customer'
                                    : 'This product description will not be shown to the customer'
        ];
    }

    /**
     *  Returns the product allow variants status and description
     */
    public function getAllowVariantsAttribute($value)
    {
        return [
            'status' => $value ? true : false,
            'name' => $value ? 'Yes' : 'No',
            'description' => $value ? 'This product uses variations'
                                    : 'This product does not use variations'
        ];
    }

    /**
     *  Returns the product allow multiple quantity per order status and description
     */
    public function getAllowMultipleQuantityPerOrderAttribute($value)
    {
        return [
            'status' => $value ? true : false,
            'name' => $value ? 'Yes' : 'No',
            'description' => $value ? 'This product can be ordered in multiple quantities per order (more than 1 per order)'
                                    : 'This product cannot be ordered in multiple quantities per order (limited to 1 quantity per order)'
        ];
    }

    /**
     *  Returns the product allow maximum quantity per order status and description
     */
    public function getAllowMaximumQuantityPerOrderAttribute($value)
    {
        return [
            'status' => $value ? true : false,
            'name' => $value ? 'Yes' : 'No',
            'description' => $value ? 'This product has maximum quantity of '.$this->maximum_quantity_per_order.' per order placed'
                                    : 'This product does not have a maximum quantity per order placed'
        ];
    }

    /**
     *  Returns the product allow stock management status and description
     */
    public function getAllowStockManagementAttribute($value)
    {
        return [
            'status' => $value ? true : false,
            'name' => $value ? 'Yes' : 'No',
            'description' => $value ? 'This product allows stock management'
                                    : 'This product does not allow stock management'
        ];
    }

    /**
     *  Returns the product allow stock management status and description
     */
    public function getAutoManageStockAttribute($value)
    {
        return [
            'status' => $value ? true : false,
            'name' => $value ? 'Yes' : 'No',
            'description' => $value ? 'This product automatically manages stock quantities'
                                    : 'This product does not automatically manage stock quantities'
        ];
    }

    /**
     *  Returns the product stock quantity status and description
     */
    public function getStockQuantityAttribute($value)
    {
        $value = ($value >= 0) ? $value : 0;

        return [
            'value' => $value,
            'description' => $value ? ($value . ' available') : 'No stock'
        ];
    }

    /**
     *  Returns the product is free status and description
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

    /**
     *  Returns the product currency code and symbol
     */
    public function getCurrencyAttribute($currency_code)
    {
        return $this->unpackCurrency($currency_code);
    }

    /**
     *  Returns the product regular price for one unit.
     */
    public function getUnitRegularPriceAttribute($value)
    {
        $amount = $this->is_free['status'] ? 0 : $value;

        return $this->convertToMoney($this->currency, $amount);
    }

    /**
     *  Returns the product sale price for one unit.
     */
    public function getUnitSalePriceAttribute($value)
    {
        $amount = $this->is_free['status'] ? 0 : $value;

        return $this->convertToMoney($this->currency, $amount);
    }

    /**
     *  Returns the product cost for one unit.
     */
    public function getUnitCostAttribute($value)
    {
        return $this->convertToMoney($this->currency, $value);
    }

    /**
     *  Re-calculates the maximum quantity per order based on the stock.
     */
    public function getMaximumQuantityPerOrderAttribute($value)
    {
        $maximum_quantity_per_order = $value;

        //  If we allow stock management
        if ($this->allow_stock_management['status']) {

            //  If maximum quantity per order is greater than the stock quantity available
            if ( $maximum_quantity_per_order > $this->stock_quantity['value']) {

                //  Return the remaining stock quantity as the maximum quantity per order
                return $this->stock_quantity['value'];

            }

        }

        //  Return original value
        return $maximum_quantity_per_order;
    }

    /**
     *  Returns the product price for one unit.
     *
     *  This is the total price of the product based 
     *  on the regular price and the sale price.
     */
    public function getUnitPriceAttribute()
    {
        //  If this is a free product
        if( $this->is_free['status'] ){

            return $this->convertToMoney($this->currency, 0);

        }else{

            //  If we have a sale price that is less than the regular price
            if (($this->unit_sale_price['amount'] != 0) && ($this->unit_sale_price['amount'] < $this->unit_regular_price['amount'])) {
    
                //  Return the sale price
                return $this->unit_sale_price;
    
            } else {
    
                //  Return the regular price
                return $this->unit_regular_price;
    
            }

        }
        
    }

    /**
     *  Returns true if the product is on sale
     */
    public function getOnSaleAttribute()
    {
        //  If we have a regular price and the sale price and if the sale price is less than the regular price
        $value = ( !$this->is_free['status'] && ($this->unit_sale_price['amount'] != 0) && ($this->unit_regular_price['amount'] != 0) && 
                 ( $this->unit_sale_price['amount'] < $this->unit_regular_price['amount'] ));

        return [
            'status' => $value,
            'name' => $value ? 'Sale' : 'No Sale',
            'description' => $value ? 'This product is on sale'
                                    : 'This product is not on sale'
        ];
    }

    /*
     *  Returns the sale percentage
     */
    public function getUnitSalePercentageAttribute()
    {
        //  If we have a sale
        if ( $this->on_sale['status'] ) {

            //  Calculate the difference
            $difference = ($this->unit_regular_price['amount'] - $this->unit_sale_price['amount']);

            $percentage = ($difference / $this->unit_regular_price['amount']) * 100;

            return round($percentage);

        }
    }

    /**
     *  Returns the product sale discount for one unit.
     *
     *  This is the difference in the regular price and sale price.
     */
    public function getUnitSaleDiscountAttribute()
    {
        $amount = 0;

        if ( $this->on_sale['status'] ) {

            //  Calculate the sale discount or amount saved
            $amount = $this->unit_regular_price['amount'] - $this->unit_sale_price['amount'];

        }

        return $this->convertToMoney($this->currency, $amount);
    }
    
    /**
     *  Returns the product profit for one unit.
     *
     *  This is the positive difference in the unit price and cost
     */
    public function getUnitProfitAttribute()
    {
        $profit = ($this->unit_price['amount'] - $this->unit_cost['amount']);

        if( $profit < 0 ){

            $profit = 0;
            
        }
        
        return $this->convertToMoney($this->currency, $profit);
    }

    /**
     *  Returns the product loss for one unit.
     *
     *  This is the negative difference in the unit price and cost
     */
    public function getUnitLossAttribute()
    {
        $loss = ($this->unit_price['amount'] - $this->unit_cost['amount']);

        //  If we don't have a loss
        if( $loss >= 0 ){

            //  Set the loss to Zero
            $loss = 0;
        
        //  If we have a loss (then the result is negative since the unit cost is greater than the unit price)
        }else{

            //  We need to convert to a positive number (i.e --50 = +50)
            $loss = -$loss;

        }
        
        return $this->convertToMoney($this->currency, $loss);
    }

    /**
     *  Returns true/false if the product has stock
     */
    public function getHasPriceAttribute()
    {   
        //  If this product is not free and the unit price is greater than 0
        $value = !$this->is_free['status'] && $this->unit_price['amount'] > 0;

        return [
            'status' => $value,
            'name' => $value ? 'Has Price' : 'No Price',
            'description' => $value ? 'This product has a price' : 'This product does not have a price'
        ];
    }

    /**
     *  Returns true/false if the product has stock
     */
    public function getHasStockAttribute()
    {   
        //  If this product does not allow stock management (Then it means we have unlimited stock)
        $unlimited = $this->allow_stock_management['status'] === false;

        if( $unlimited ){

            return [
                'status' => $unlimited,
                'name' => 'Unlimited Stock',
                'description' => 'This product has unlimited stock'
            ];

        }else{
            
            //  If this product does not allow stock management or the product allows stock management and has stock quantity
            $status = ($this->allow_stock_management && $this->stock_quantity['value'] > 0);

            return [
                'status' => $status,
                'name' => $status ? 'Has Stock' : 'No Stock',
                'description' => $status ? 'This product has limited stock' : 'This product does not have stock'
            ];

        }
        
    }

    public function setShowDescriptionAttribute($value)
    {
        if( is_array($value) ){
            $this->attributes['show_description'] = (in_array($value['status'], ['true', true, '1', 1]) ? 1 : 0);
        }else{
            $this->attributes['show_description'] = (($value == 'true' || $value == '1') ? 1 : 0);
        }
    }

    public function setVisibleAttribute($value)
    {
        if( is_array($value) ){
            $this->attributes['visible'] = (in_array($value['status'], ['true', true, '1', 1]) ? 1 : 0);
        }else{
            $this->attributes['visible'] = (($value == 'true' || $value == '1') ? 1 : 0);
        }
    }

    public function setAllowVariantsAttribute($value)
    {
        if( is_array($value) ){
            $this->attributes['allow_variants'] = (in_array($value['status'], ['true', true, '1', 1]) ? 1 : 0);
        }else{
            $this->attributes['allow_variants'] = (($value == 'true' || $value == '1') ? 1 : 0);
        }
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

    public function setUnitCostAttribute($value)
    {
        $this->attributes['unit_cost'] = is_array($value) ? $value['amount'] : $value;
    }

    public function setAllowMultipleQuantityPerOrderAttribute($value)
    {
        if( is_array($value) ){
            $this->attributes['allow_multiple_quantity_per_order'] = (in_array($value['status'], ['true', true, '1', 1]) ? 1 : 0);
        }else{
            $this->attributes['allow_multiple_quantity_per_order'] = (($value == 'true' || $value == '1') ? 1 : 0);
        }
    }

    public function setAllowMaximumQuantityPerOrderAttribute($value)
    {
        if( is_array($value) ){
            $this->attributes['allow_maximum_quantity_per_order'] = (in_array($value['status'], ['true', true, '1', 1]) ? 1 : 0);
        }else{
            $this->attributes['allow_maximum_quantity_per_order'] = (($value == 'true' || $value == '1') ? 1 : 0);
        }
    }

    public function getVariantAttributesAttribute($value)
    {
        //  Convert to array
        return is_null($value) ? [] : json_decode($value, true);
    }

    public function setAllowStockManagementAttribute($value)
    {
        if( is_array($value) ){
            $this->attributes['allow_stock_management'] = (in_array($value['status'], ['true', true, '1', 1]) ? 1 : 0);
        }else{
            $this->attributes['allow_stock_management'] = (($value == 'true' || $value == '1') ? 1 : 0);
        }
    }

    public function setAutoManageStockAttribute($value)
    {
        if( is_array($value) ){
            $this->attributes['auto_manage_stock'] = (in_array($value['status'], ['true', true, '1', 1]) ? 1 : 0);
        }else{
            $this->attributes['auto_manage_stock'] = (($value == 'true' || $value == '1') ? 1 : 0);
        }
    }

    public function setStockQuantityAttribute($value)
    {
        $this->attributes['stock_quantity'] = is_array($value) ? $value['value'] : $value;
    }

    //  ON DELETE EVENT
    public static function boot()
    {
        try {
            parent::boot();

            // before delete() method call this
            static::deleting(function ($product) {
                //  Delete all variations
                foreach ($product->variations as $variation) {
                    $variation->delete();
                }

                //  Delete all variables
                $product->variables()->delete();

                //  Delete all records of this product being assigned to specific locations, instant carts, etc
                DB::table('product_allocations')->where(['product_id' => $product->id])->delete();

                // do the rest of the cleanup...
            });
        } catch (\Exception $e) {
            throw($e);
        }
    }
}
