<?php

namespace App;

use App\Traits\CommonTraits;
use Illuminate\Database\Eloquent\Model;

class Discount extends Model
{
    use CommonTraits;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $casts = [
        
        'is_fixed_rate' => 'boolean',
        'is_percentage_rate' => 'boolean'

    ];

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name', 'description', 'is_fixed_rate', 'fixed_rate', 'is_percentage_rate', 'percentage_rate', 'store_id'
    ];

    /*
     *  Returns the store of this discount
     */
    public function store()
    {
        return $this->belongsTo('App\Store', 'store_id');
    }

    /*
     *  Returns the list of products that this discount has been assigned to
     */
    public function products()
    {
        return $this->belongsToMany('App\Product');
    }

    /** ATTRIBUTES
     * 
     *  Note that the "resource_type" is defined within CommonTraits
     * 
     */
    protected $appends = [
        'resource_type',
    ];

    public function setIsFixedRateAttribute($value)
    {
        $this->attributes['is_fixed_rate'] = (($value == 'true' || $value == '1') ? 1 : 0);
    }

    public function setIsPercentageRateAttribute($value)
    {
        $this->attributes['is_percentage_rate'] = (($value == 'true' || $value == '1') ? 1 : 0);
    }

}
