<?php

namespace App;

use App\Traits\CommonTraits;
use App\Traits\CurrencyTraits;
use Illuminate\Database\Eloquent\Model;

class Currency extends Model
{
    use CurrencyTraits, CommonTraits;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [

        'code', 'symbol'

    ];

    /* ATTRIBUTES */

    protected $appends = [
        'resource_type'
    ];

}
