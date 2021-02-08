<?php

namespace App;

use App\Traits\StatusTraits;
use App\Traits\CommonTraits;
use Illuminate\Database\Eloquent\Model;

class Status extends Model
{
    use CommonTraits, StatusTraits;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [

        /*  Status Info  */
        'name', 'type', 'description'

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
