<?php

namespace App;

use App\Traits\CommonTraits;
use Illuminate\Database\Eloquent\Model;

class Favourite extends Model
{
    use CommonTraits;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'location_id', 'user_id'
    ];

    /*
     *  Returns the location of this favourite
     */
    public function location()
    {
        return $this->belongsTo('App\Location', 'location_id');
    }

    /*
     *  Returns the user of this favourite
     */
    public function user()
    {
        return $this->belongsTo('App\User', 'user_id');
    }

}
