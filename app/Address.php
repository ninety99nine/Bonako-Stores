<?php

namespace App;

use App\Traits\CommonTraits;
use App\Traits\AddressTraits;
use Illuminate\Database\Eloquent\Model;

class Address extends Model
{
    use CommonTraits, AddressTraits;

    protected $with = ['addressType'];

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [

        /*  Basic Info  */
        'name', 'mobile_number', 'physical_address', 'address_type_id',

        /*  Ownership Information  */
        'owner_id', 'owner_type'

    ];

    /**
     * Get the owning resource e.g User, Location
     */
    public function owner()
    {
        return $this->morphTo();
    }

    /**
     *  Returns the address type
     */
    public function addressType()
    {
        return $this->belongsTo('App\AddressType');
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
