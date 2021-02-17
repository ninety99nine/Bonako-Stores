<?php

namespace App;

use App\Traits\CommonTraits;
use App\Traits\ProductTypeTraits;
use Illuminate\Database\Eloquent\Model;

class ProductType extends Model
{
    use CommonTraits, ProductTypeTraits;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [

        /*  Product Type Info  */
        'name', 'description'

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
