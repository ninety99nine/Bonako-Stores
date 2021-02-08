<?php

namespace App;

use App\Traits\CommonTraits;
use App\Traits\AddressTypeTraits;
use Illuminate\Database\Eloquent\Model;

class AddressType extends Model
{
    use CommonTraits, AddressTypeTraits;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [

        /*  Basic Info  */
        'name'

    ];

    /** ATTRIBUTES
     *
     *  Note that the "resource_type" is defined within CommonTraits
     *
     */
    protected $appends = [
        'resource_type',
    ];

}
