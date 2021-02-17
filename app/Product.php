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

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $casts = [
        'show_description' => 'boolean',
        'visible' => 'boolean',
        'allow_variants' => 'boolean',
        'variant_attributes' => 'array',
        'allow_multiple_quantity_per_order' => 'boolean',
        'allow_maximum_quantity_per_order' => 'boolean',
        'allow_stock_management' => 'boolean',
        'auto_manage_stock' => 'boolean',
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
        'unit_regular_price', 'unit_sale_price', 'unit_cost',

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
        'resource_type', 'unit_price', 'unit_sale_discount', 'unit_profit', 'on_sale',
    ];

    /**
     *  Re-calculates the maximum quantity per order based on the stock.
     *
     */
    public function getMaximumQuantityPerOrderAttribute($value)
    {
        $maximum_quantity_per_order = $value;

        //  If we allow stock management
        if ($this->allow_stock_management) {

            //  If maximum quantity per order is greater than the stock quantity available
            if ( $maximum_quantity_per_order > $this->stock_quantity) {

                //  Return the remaining stock quantity as the maximum quantity per order
                return $this->stock_quantity;

            }

        }

        //  Return original value
        return $maximum_quantity_per_order;
    }

    /**
     *  Returns the product price for one unit.
     *
     *  This is the total price of the product based on the regular
     *  price and the sale price.
     */
    public function getUnitPriceAttribute()
    {
        //  If we have a regular price, then we can either return the regular price or sale price
        if (!is_null($this->unit_regular_price)) {

            //  If we have a sale price that is less than the regular price
            if (!empty($this->unit_sale_price) && $this->unit_sale_price < $this->unit_regular_price) {

                //  Return the sale price
                return $this->unit_sale_price;

            } else {

                //  Return the regular price
                return $this->unit_regular_price;

            }
        }

        return null;
    }

    /*
     *  Returns true if the product is on sale
     */
    public function getOnSaleAttribute()
    {
        //  If we have a regular price and the sale price and if the sale price is less than the regular price
        if ( !empty($this->unit_regular_price) && !empty($this->unit_sale_price) &&
             ($this->unit_sale_price < $this->unit_regular_price) ) {

            return true;

        }

        return false;
    }

    /**
     *  Returns the product sale discount for one unit.
     *
     *  This is the difference in the regular price and sale price.
     */
    public function getUnitSaleDiscountAttribute()
    {
        if ( $this->on_sale ) {

            //  Calculate the sale discount or amount saved
            return $this->unit_regular_price - $this->unit_sale_price;

        }

        return 0;
    }

    /**
     *  Returns the product profit for one unit.
     *
     *  This is the difference in the regular price and sale price.
     */
    public function getUnitProfitAttribute()
    {
        $profit = ($this->unit_price - $this->unit_cost - $this->unit_sale_discount);

        if( $profit >= 0 ){

            return $profit;

        }

        return 0;
    }

    public function setShowDescriptionAttribute($value)
    {
        $this->attributes['show_description'] = (($value == 'true' || $value == '1') ? 1 : 0);
    }

    public function setVisibleAttribute($value)
    {
        $this->attributes['visible'] = (($value == 'true' || $value == '1') ? 1 : 0);
    }

    public function setAllowVariantsAttribute($value)
    {
        $this->attributes['allow_variants'] = (($value == 'true' || $value == '1') ? 1 : 0);
    }

    public function setAllowMultipleQuantityPerOrderAttribute($value)
    {
        $this->attributes['allow_multiple_quantity_per_order'] = (($value == 'true' || $value == '1') ? 1 : 0);
    }

    public function setAllowMaximumQuantityPerOrderAttribute($value)
    {
        $this->attributes['allow_maximum_quantity_per_order'] = (($value == 'true' || $value == '1') ? 1 : 0);
    }

    public function getVariantAttributesAttribute($value)
    {
        //  Convert to array
        return is_null($value) ? [] : json_decode($value, true);
    }

    public function setAllowStockManagementAttribute($value)
    {
        $this->attributes['allow_stock_management'] = (($value == 'true' || $value == '1') ? 1 : 0);
    }

    public function setAutoManageStockAttribute($value)
    {
        $this->attributes['auto_manage_stock'] = (($value == 'true' || $value == '1') ? 1 : 0);
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
