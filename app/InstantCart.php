<?php

namespace App;

use Carbon\Carbon;
use App\Traits\CommonTraits;
use App\Traits\InstantCartTraits;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\Relation;

class InstantCart extends Model
{
    use CommonTraits, InstantCartTraits;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $casts = [
        'active' => 'boolean'
    ];

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [

        /*  Basic Info  */
        'code', 'name', 'description',

        /*  Status  */
        'active',

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

    /**
     *  Returns the the coupons assigned to this instant cart
     */
    public function coupons()
    {
        return $this->morphToMany('App\Coupon', 'owner', 'coupon_allocations');
    }

    /**
     *  Returns the the products assigned to this instant cart
     */
    public function products()
    {
        return $this->morphToMany('App\Product', 'owner', 'product_allocations')
                    ->withTimestamps()->withPivot('quantity')->orderBy('product_allocations.arrangement');
    }

    /** ATTRIBUTES
     * 
     *  Note that the "resource_type" is defined within CommonTraits
     * 
     */
    protected $appends = [
        'resource_type', 'short_code', 'cart'
    ];

    /*
     *  Returns a shortcode version of the instant cart code
     */
    public function getShortCodeAttribute()
    {
        return '*250*'.$this->code.'#';
    }

    /*
     *  Returns a shortcode version of the instant cart code
     */
    public function getCartAttribute()
    {
        //  Retrieve the product ids and quantity
        $items = collect($this->products)->map(function($product){
            return [
                'id' => $product->id,
                'quantity' => $product->pivot->quantity
            ];
        });

        //  Retrieve the ids of the coupons we want to apply
        $coupon_ids = collect($this->coupons)->map(function($coupon){
            return $coupon->id;
        });

        //  Retrieve the store coupons available for use against the coupons we want to apply
        $store_coupons = collect($this->coupons)->toArray();

        //  Set the cart details (Cart Items + Store Coupons)
        $info = [
            'items' => $items,
            'coupon_ids' => $coupon_ids,
            'store_coupons' => $store_coupons
        ];

        //  Build and return the cart details
        $cart = (new MyCart)->getCartDetails($info);

        return $cart;
    }

    public function setActiveAttribute($value)
    {
        $this->attributes['active'] = (($value == 'true' || $value === '1') ? 1 : 0);
    }

}
