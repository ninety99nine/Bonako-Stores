<?php

namespace App;

use DB;
use App\Traits\UserTraits;
use App\Traits\CommonTraits;
use Laravel\Passport\HasApiTokens;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Builder;
use \Staudenmeir\EloquentHasManyDeep\HasRelationships;
use Illuminate\Foundation\Auth\User as Authenticatable;

class User extends Authenticatable
{
    use HasRelationships, HasApiTokens, Notifiable, UserTraits, CommonTraits;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'first_name', 'last_name', 'email', 'mobile_number', 'mobile_number_verified_at',
        'email_verified_at', 'account_type', 'password',
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

    /*
     *  Scope:
     *  Returns users that are being searched
     */
    public function scopeSearch($query, $searchTerm)
    {
        //  Remove spaces from the search term
        $searchTerm = str_replace(' ', '', $searchTerm);

        return $query->where('mobile_number', 'like', "%".$searchTerm."%")
                     ->orWhere('mobile_number', 'like', "%267".$searchTerm."%")
                     ->orWhere(DB::raw("CONCAT(`first_name`, `last_name`)"), 'LIKE', "%".$searchTerm."%");
    }

    /*
     *  Scope:
     *  Returns users that are being searched
     */
    public function scopeSearchMobile($query, $searchTerm)
    {
        return $query->where('mobile_number', $searchTerm)->orWhere('mobile_number', "267".$searchTerm);
    }

    /**
     *  Scope:
     *  Returns users that have orders
     */
    public function scopeHasOrders($query)
    {
        return $query->whereHas('orders');
    }

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
                ->distinct('stores.id');

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

    /**
     *  The addresses that belong to this user
     */
    public function addresses()
    {
        return $this->morphMany(Address::class, 'owner')->latest();
    }

    /**
     *  Returns the customers orders
     */
    public function orders()
    {
        return $this->hasMany('App\Order', 'customer_id');
    }

    /** ATTRIBUTES
     *
     *  Note that the "resource_type" is defined within CommonTraits.
     */
    protected $appends = [
        'resource_type', 'name', 'requires_password', 'requires_mobile_number_verification'
    ];

    public function getNameAttribute($value)
    {
        return trim($this->first_name.' '.$this->last_name);
    }

    public function getRequiresPasswordAttribute($value)
    {
        return empty($this->password);
    }

    public function getRequiresMobileNumberVerificationAttribute($value)
    {
        return empty($this->mobile_number_verified_at);
    }

    public function getMobileNumberAttribute($value)
    {
        return [
            'number' => substr($value, 3),
            'code' => substr($value, 0, 3),
            'number_with_code' => $value,
            'calling_number' => '+'.$value
        ];
    }

    public function setDeliveryTypeAttribute($value)
    {
        $first_letter = strtolower(substr($value, 0, 1));

        //  If the first letter is "d" or "p" which stands for "delivery" or "pickup"
        if( in_array($first_letter, ['d', 'p']) ){

            //  Set the value
            $this->attributes['delivery_type'] = $first_letter;

        }else{

            //  Set default value
            $this->attributes['delivery_type'] = 'd';

        }
    }

}
