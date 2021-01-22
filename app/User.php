<?php

namespace App;

use App\Traits\UserTraits;
use Illuminate\Foundation\Auth\User as Authenticatable;
use \Staudenmeir\EloquentHasManyDeep\HasRelationships;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Notifications\Notifiable;
use Laravel\Passport\HasApiTokens;

class User extends Authenticatable
{
    use HasRelationships;
    use HasApiTokens;
    use Notifiable;
    use UserTraits;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'first_name', 'last_name', 'email', 'mobile_number', 'mobile_number_verification_code', 'account_type',
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
     *  The stores that the user is assigned to are retrieved by first
     *  retrieving the locations that the user is assigned to, then
     *  returning the distinct stores that own those locations.
     *
     *  To fetch the stores, we first must target the locations that the
     *  user has been assigned to
     *
     *  The stores that are either created or shared with this user.
     *  Return each store with any favourite records that show
     *  whether the user marked the store as a favourite
     *
     *  User → many to many → Locations → has many → Stores
     */
    public function stores()
    {
        return $this->hasManyDeep(
                    'App\Store',
                    ['location_user', 'App\Location'],
                    //  Foreign keys
                    [
                        'user_id',      // (Link User to location_user) Foreign key on the "location_user" table.
                        'id',           // (Link location_user to Location) Foreign key on the "locations" table (local key).
                        'id'            // (Link Location to Store) Foreign key on the "stores" table.
                    ],
                    //  Local keys
                    [
                        'id',           // (Link User to location_user) Local key on the "users" table.
                        'location_id',  // (Link location_user to Location) Local key on the "location_user" table (foreign key).
                        'store_id'      // (Link Location to Store) Local key on the "locations" table.
                    ]

                /** Since we want to be able to know if this is the user's favourite store,
                 *  then we must eager load and count the total number of locations that
                 *  have been marked as the user's favourite.
                 */
                )->withCount(['locations as total_favourite_locations' => function (Builder $query) {
                    $query->whereHas('favourites', function (Builder $query) {
                                $query->where('user_id', auth()->user()->id);
                            });
                }])

                //  Order by the latest stores
                ->latest()

                //  Avoid any duplicate stores
                ->distinct();

    }

    /****************************
     *   Subscriptions          *
     ****************************/

    /**
     *  The subscriptions that belong to this user.
     */
    public function subscriptions()
    {
        return $this->hasMany('App\Subscription');
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
