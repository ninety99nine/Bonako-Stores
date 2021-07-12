<?php

namespace App;

use Carbon\Carbon;
use App\Traits\CommonTraits;
use App\Traits\PopularStoreTraits;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;

class PopularStore extends Model
{
    use PopularStoreTraits, CommonTraits;

    protected $with = ['store'];

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'store_id', 'arrangement', 'start_at', 'end_at'
    ];

    /*
     *  Scope:
     *  Returns popular stores that are being searched
     */
    public function scopeSearch($query, $searchTerm)
    {
        return $query->whereHas('store', function (Builder $query) use ($searchTerm){
            $query->search($searchTerm);
        });
    }

    /*
     *  Scope:
     *  Returns popular stores that are visible
     */
    public function scopeVisible($query)
    {
        return $query->where('start_at', '<', Carbon::now())->where('end_at', '>', Carbon::now());
    }

    /*
     *  Scope:
     *  Returns popular stores that are not visible
     */
    public function scopeInVisible($query)
    {
        return $query->where('start_at', '>', Carbon::now())->orWhere('end_at', '<', Carbon::now());
    }

    /*
     *  Scope:
     *  Returns popular stores that are expired
     */
    public function scopeExpired($query)
    {
        return $query->where('end_at', '<', Carbon::now());
    }

    /*
     *  Returns the store
     */
    public function store()
    {
        return $this->belongsTo('App\Store', 'store_id');
    }

    /** ATTRIBUTES
     *
     *  Note that the "resource_type" is defined within CommonTraits.
     */
    protected $appends = [
        'resource_type', 'visible', 'has_expired'
    ];

    /**
     *  Returns true/false if the popular store is visible
     */
    public function getVisibleAttribute()
    {
        $start_at_exceeded = \Carbon\Carbon::parse($this->start_at)->isPast();
        $end_at_exceeded = \Carbon\Carbon::parse($this->end_at)->isPast();

        $visible = ($start_at_exceeded && !$end_at_exceeded);

        //  If this is visible
        if( $visible ){

            $description = 'This store is visible and currently listed as a popular store';

        //  If the start datetime has not elapsed
        }elseif(!$start_at_exceeded){

            $description = 'This store is hidden because it has not reached it start date';

        //  If the end datetime has elapsed
        }elseif($end_at_exceeded){

            $description = 'This store is hidden because it has exceeded its end date';

        }

        return [
            'status' => $visible ? true : false,
            'name' => $visible ? 'Visible' : 'Hidden',
            'description' => $description
        ];

    }

    /**
     *  Returns true/false if the popular store has expired
     */
    public function getHasExpiredAttribute()
    {
        return (\Carbon\Carbon::parse($this->end_at)->isPast());
    }

}
