<?php

namespace App;

use App\Traits\CommonTraits;
use Illuminate\Database\Eloquent\Model;

class LocationRating extends Model
{
    use CommonTraits;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'value', 'comment', 'user_id', 'location_id'
    ];

    /** ATTRIBUTES
     *
     *  Note that the "resource_type" is defined within CommonTraits.
     */
    protected $appends = [
        'resource_type',
    ];
}
