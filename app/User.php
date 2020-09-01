<?php

namespace App;

use App\Traits\UserTraits;
use Laravel\Passport\HasApiTokens;
use Illuminate\Notifications\Notifiable;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Foundation\Auth\User as Authenticatable;

class User extends Authenticatable
{
    use HasApiTokens, Notifiable, UserTraits;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name', 'email', 'password', 'account_type'
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

    /**
     *  Checks if a given user is a Super Admin
     */
    public function isSuperAdmin()
    {
        return $this->account_type == 'superadmin';
    }

    /**
     *  The stores that this user created
     */
    public function createdStores()
    {
        return $this->hasMany('App\Store', 'user_id');
    }

    /**
     *  The stores that were shared with this user
     *  NOTE: These stores are assigned but not 
     *  created by the user
     */
    public function sharedStores()
    {
        return $this->stores()
                    ->where('user_id', '!=', auth()->user()->id)
                    ->where('store_user.user_id', '=', auth()->user()->id);
    }

    /**
     *  The stores that are either created or shared with this user
     */
    public function stores()
    {
        return $this->belongsToMany('App\Store')->withPivot(['user_id', 'store_id', 'type']);
    }

    /**
     *  The locations that are either created or shared with this user
     */
    public function locations()
    {
        return $this->belongsToMany('App\Location')->withPivot(['user_id', 'location_id', 'type']);
    }
    
}
