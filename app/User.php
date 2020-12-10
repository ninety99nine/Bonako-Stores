<?php

namespace App;

use App\Traits\UserTraits;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Passport\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens;
    use Notifiable;
    use UserTraits;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'first_name', 'last_name', 'email', 'mobile_number', 'password', 'account_type',
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password', 'remember_token',
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    /****************************
     *   STORES                 *
     ****************************/

    /**
     *  The stores that are either created or shared with this user.
     *  Return each store with any favourite records that show
     *  whether the user marked the store as a favourite
     */
    public function stores()
    {
        return $this->belongsToMany('App\Store')->withPivot(['user_id', 'store_id', 'type'])
                    ->with(['favourites' => function ($hasMany) {
                        $hasMany->where('user_id', auth()->user()->id);
                    }])->latest();
    }

    /****************************
     *   LOCATIONS              *
     ****************************/

    /**
     *  The locations that are either created or shared with this user.
     */
    public function locations()
    {
        return $this->belongsToMany('App\Location')->withPivot(['user_id', 'location_id', 'type']);
    }

    /**
     *  The locations that this user created.
     */
    public function createdLocations()
    {
        return $this->hasMany('App\Location', 'user_id');
    }

    /**
     *  The locations that were shared with this user
     *  NOTE: These locations are assigned but not
     *  created by the user.
     */
    public function sharedLocations()
    {
        return $this->locations()
                    ->where('user_id', '!=', auth()->user()->id)
                    ->where('location_user.user_id', '=', auth()->user()->id);
    }
    
}
