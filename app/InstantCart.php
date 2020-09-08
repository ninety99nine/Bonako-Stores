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
        'resource_type', 'short_code'
    ];

    /*
     *  Returns a shortcode version of the instant cart code
     */
    public function getShortCodeAttribute()
    {
        return '*250*'.$this->code.'#';
    }

    public function setActiveAttribute($value)
    {
        $this->attributes['active'] = (($value == 'true' || $value === '1') ? 1 : 0);
    }

}
