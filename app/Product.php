<?php

namespace App;

use App\Traits\CommonTraits;
use App\Traits\ProductTraits;
use DB;
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
        'active' => 'boolean',
        'is_new' => 'boolean',
        'is_featured' => 'boolean',
        'show_on_store' => 'boolean',
        'allow_variants' => 'boolean',
        'allow_downloads' => 'boolean',
        'variant_attributes' => 'array',
        'auto_manage_stock' => 'boolean',
        'allow_stock_management' => 'boolean',
    ];

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        //  Product Details
        'name', 'description', 'active', 'type', 'cost_per_item', 'unit_regular_price', 'unit_sale_price',
        'sku', 'barcode', 'stock_quantity', 'allow_stock_management', 'auto_manage_stock',
        'variant_attributes', 'allow_variants', 'allow_downloads', 'show_on_store',
        'is_new', 'is_featured', 'parent_product_id', 'user_id', 'store_id',
    ];

    /*
     *  Scope:
     *  Returns products that are not variables of another product
     */
    public function scopeIsNotVariation($query)
    {
        return $query->whereNull('parent_product_id');
    }

    /**
     *  Returns the list of locations that this product belongs to.
     * 
     *   public function locations()
     *   {
     *       return $this->belongsToMany('App\Location');
     *   }
     */

    /**
     *  Returns the locations that this product is assigned to
     */
    public function locations()
    {
        return $this->morphedByMany('App\Location', 'owner');
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
        'resource_type', 'unit_price', 'discount', 'on_sale',
    ];

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
            if (!is_null($this->unit_sale_price) && $this->unit_sale_price < $this->unit_regular_price) {
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
        if (!is_null($this->unit_regular_price) && !is_null($this->unit_sale_price) && ($this->unit_sale_price < $this->unit_regular_price)) {
            return true;
        }

        return false;
    }

    /**
     *  Returns the product discount for one unit.
     *
     *  This is the difference in the regular price and sale price.
     */
    public function getDiscountAttribute()
    {
        if ($this->on_sale) {
            //  Calculate the discount or amount saved
            return $this->unit_regular_price - $this->unit_sale_price;
        }

        return 0;
    }

    public function setActiveAttribute($value)
    {
        $this->attributes['active'] = (($value == 'true' || $value === '1') ? 1 : 0);
    }

    public function setAllowStockManagementAttribute($value)
    {
        $this->attributes['allow_stock_management'] = (($value == 'true' || $value == '1') ? 1 : 0);
    }

    public function setAutoManageStockAttribute($value)
    {
        $this->attributes['auto_manage_stock'] = (($value == 'true' || $value == '1') ? 1 : 0);
    }

    public function setAllowVariantsAttribute($value)
    {
        $this->attributes['allow_variants'] = (($value == 'true' || $value == '1') ? 1 : 0);
    }

    public function setAllowDownloadsAttribute($value)
    {
        $this->attributes['allow_downloads'] = (($value == 'true' || $value == '1') ? 1 : 0);
    }

    public function setShowOnStoreAttribute($value)
    {
        $this->attributes['show_on_store'] = (($value == 'true' || $value == '1') ? 1 : 0);
    }

    public function setIsNewAttribute($value)
    {
        $this->attributes['is_new'] = (($value == 'true' || $value == '1') ? 1 : 0);
    }

    public function setIsFeaturedAttribute($value)
    {
        $this->attributes['is_featured'] = (($value == 'true' || $value == '1') ? 1 : 0);
    }

    //  ON DELETE EVENT
    public static function boot()
    {
        parent::boot();

        // before delete() method call this
        static::deleting(function ($product) {
            //  Delete all variations
            foreach ($product->variations as $variation) {
                $variation->delete();
            }

            //  Delete all variables
            $product->variables()->delete();

            //  Delete all discounts
            $product->discounts()->delete();

            //  Delete all coupons
            $product->coupons()->delete();

            //  Delete all records of this product being assigned to specific locations
            DB::table('location_product')->where(['product_id' => $product->id])->delete();

            // do the rest of the cleanup...
        });
    }
}
