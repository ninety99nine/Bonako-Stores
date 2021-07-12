<?php

namespace App;

use Carbon\Carbon;
use App\Traits\CommonTraits;
use App\Traits\AdvertTraits;
use Illuminate\Database\Eloquent\Model;

class Advert extends Model
{
    use AdvertTraits, CommonTraits;

    protected $with = ['owner'];

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name', 'title', 'message', 'call_to_action', 'arrangement', 'allow_limited_views', 'limited_views',
        'start_at', 'end_at',

        'owner_id', 'owner_type'
    ];

    /*
     *  Scope:
     *  Returns adverts that are being searched
     */
    public function scopeSearch($query, $searchTerm)
    {
        return $query
                ->where('id', $searchTerm)
                ->orWhere('name', 'like', '%'.$searchTerm.'%')
                ->orWhere('title', 'like', '%'.$searchTerm.'%')
                ->orWhere('message', 'like', '%'.$searchTerm.'%');
    }

    /*
     *  Scope:
     *  Returns adverts that are visible
     */
    public function scopeVisible($query)
    {
        return $query->where('start_at', '<', Carbon::now())->where('end_at', '>', Carbon::now());
    }

    /*
     *  Scope:
     *  Returns adverts that are not visible
     */
    public function scopeInVisible($query)
    {
        return $query->where('start_at', '>', Carbon::now())->orWhere('end_at', '<', Carbon::now());
    }

    /*
     *  Scope:
     *  Returns adverts that are expired
     */
    public function scopeExpired($query)
    {
        return $query->where('end_at', '<', Carbon::now());
    }

    /**
     * Get the owning resource e.g Store, Location, Instant Cart
     */
    public function owner()
    {
        return $this->morphTo();
    }

    /** ATTRIBUTES
     *
     *  Note that the "resource_type" is defined within CommonTraits.
     */
    protected $appends = [
        'resource_type', 'visible', 'has_expired'
    ];

    /**
     *  Returns true/false if the advert is visible
     */
    public function getVisibleAttribute()
    {
        $start_at_exceeded = \Carbon\Carbon::parse($this->start_at)->isPast();
        $end_at_exceeded = \Carbon\Carbon::parse($this->end_at)->isPast();

        $visible = ($start_at_exceeded && !$end_at_exceeded);

        //  If this is visible
        if( $visible ){

            $description = 'This advert is visible and currently listed';

        //  If the start datetime has not elapsed
        }elseif(!$start_at_exceeded){

            $description = 'This advert is hidden because it has not reached it start date';

        //  If the end datetime has elapsed
        }elseif($end_at_exceeded){

            $description = 'This advert is hidden because it has exceeded its end date';

        }

        return [
            'status' => $visible ? true : false,
            'name' => $visible ? 'Visible' : 'Hidden',
            'description' => $description
        ];

    }

    /**
     *  Returns true/false if the advert has expired
     */
    public function getHasExpiredAttribute()
    {
        return (\Carbon\Carbon::parse($this->end_at)->isPast());
    }

}
