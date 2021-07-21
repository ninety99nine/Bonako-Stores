<?php

namespace App;

use DB;
use Carbon\Carbon;
use App\Traits\CommonTraits;
use App\Traits\InstantCartTraits;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;

class InstantCart extends Model
{
    use CommonTraits, InstantCartTraits;

    protected $with = ['visitShortCode', 'paymentShortCode', 'cart'];

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [

        /*  Status  */
        'active',

        /*  Basic Info  */
        'name', 'description',

        /*  Stock Management  */
        'allow_stock_management', 'stock_quantity',

        /*  Location Info  */
        'location_id'

    ];

    protected $allowedFilters = [];

    protected $allowedOrderableColumns = [];

    /**
     *  Scope:
     *  Returns instant carts that are being searched
     */
    public function scopeSearch($query, $searchTerm)
    {
        return $query->where('id', $searchTerm)->orWhere('name', 'like', '%'.$searchTerm.'%');
    }

    /**
     *  Scope:
     *  Returns instant carts that are active
     */
    public function scopeActive($query)
    {
        return $query->whereActive('1');
    }

    /**
     *  Scope:
     *  Returns instant carts that are not active
     */
    public function scopeInActive($query)
    {
        return $query->whereActive('0');
    }

    /**
     *  Scope:
     *  Returns instant carts that are expired
     */
    public function scopeExpired($query)
    {
        return $query->doesntHave('visitShortCode');
    }

    /**
     *  Scope:
     *  Returns instant carts that offer free delivery
     *  Free delivery is available if the cart offers
     *  it directly or indirectly via its coupon lines
     */
    public function scopeOffersFreeDelivery($query)
    {
        return $query->whereHas('cart', function (Builder $query) {
            $query->offersFreeDelivery();
        });
    }

    /**
     *  Returns the owning location
     */
    public function location()
    {
        return $this->belongsTo('App\Location', 'location_id');
    }

    /**
     *  Returns the cart owned by this instant cart
     */
    public function cart()
    {
        return $this->morphOne(Cart::class, 'owner');
    }

    /**
     *  Returns subscriptions of this instant cart
     */
    public function subscriptions()
    {
        return $this->morphMany(Subscription::class, 'owner')->latest();
    }

    /**
     *  Returns subscription of this instant cart
     */
    public function subscription()
    {
        return $this->morphOne(Subscription::class, 'owner')->latest();
    }

    /**
     *  Returns the short codes owned by this instant cart
     *  Only short codes that are not expired are valid.
     */
    public function shortCodes()
    {
        return $this->morphMany(ShortCode::class, 'owner')->where('expires_at', '>', Carbon::now())->latest();
    }

    /**
     *  Returns the short code owned by this instant cart
     *  Only short codes that are not expired are valid.
     */
    public function shortCode()
    {
        return $this->morphOne(ShortCode::class, 'owner')->where('expires_at', '>', Carbon::now())->latest();
    }

    /**
     *  Returns the visit short codes owned by this instant cart
     */
    public function visitShortCode()
    {
        return $this->shortCode()->where('action', 'visit');
    }

    /**
     *  Returns the payment short codes owned by this instant cart
     */
    public function paymentShortCode()
    {
        return $this->shortCode()->where('action', 'payment');
    }

    /** ATTRIBUTES
     *
     *  Note that the "resource_type" is defined within CommonTraits
     *
     */
    protected $appends = [
        'resource_type'
    ];

    /**
     *  Returns the instant cart active status and description
     */
    public function getActiveAttribute($value)
    {
        return [
            'status' => $value ? true : false,
            'name' => $value ? 'Active' : 'Inactive',
            'description' => $value ? 'This instant cart is active'
                                    : 'This instant cart is not active'
        ];
    }

    /**
     *  Returns the instant cart allow stock management status and description
     */
    public function getAllowStockManagementAttribute($value)
    {
        return [
            'status' => $value ? true : false,
            'name' => $value ? 'Yes' : 'No',
            'description' => $value ? 'This instant cart allows stock management'
                                    : 'This instant cart does not allow stock management'
        ];
    }

    /**
     *  Returns the instant cart stock quantity status and description
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
     *  Returns true/false if the instant cart has stock
     */
    public function getHasStockAttribute()
    {
        //  If this instant cart does not allow stock management (Then it means we have unlimited stock)
        $unlimited = $this->allow_stock_management['status'] === false;

        if( $unlimited ){

            return [
                'status' => $unlimited,
                'type' => 'unlimited_stock',
                'name' => 'Unlimited Stock',
                'description' => 'This instant cart has unlimited stock'
            ];

        }else{

            //  If this instant cart does not allow stock management or the instant cart allows stock management and has stock quantity
            $status = ($this->allow_stock_management && $this->stock_quantity['value'] > 0);

            return [
                'status' => $status,
                'type' => $status ? 'has_stock' : 'no_stock',
                'name' => $status ? 'Has Stock' : 'No Stock',
                'description' => $status ? 'This instant cart has limited stock' : 'This instant cart does not have stock'
            ];

        }

    }

    public function setActiveAttribute($value)
    {
        if( is_array($value) ){
            $this->attributes['active'] = (in_array($value['status'], ['true', true, '1', 1]) ? 1 : 0);
        }else{
            $this->attributes['active'] = (in_array($value, ['true', true, '1', 1]) ? 1 : 0);
        }
    }

    public function setAllowStockManagementAttribute($value)
    {
        if( is_array($value) ){
            $this->attributes['allow_stock_management'] = (in_array($value['status'], ['true', true, '1', 1]) ? 1 : 0);
        }else{
            $this->attributes['allow_stock_management'] = (in_array($value, ['true', true, '1', 1]) ? 1 : 0);
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
            static::deleting(function ($instant_cart) {

                //  Delete cart
                $instant_cart->cart()->delete();

                //  Expire short codes (CommanTraits)
                $instant_cart->expireShortCodes();

                // do the rest of the cleanup...
            });

        } catch (\Exception $e) {
            throw($e);
        }
    }

}
