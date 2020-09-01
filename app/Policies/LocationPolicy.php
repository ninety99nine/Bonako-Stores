<?php

namespace App\Policies;

use App\User;
use App\Location;
use Illuminate\Auth\Access\HandlesAuthorization;

class LocationPolicy
{
    use HandlesAuthorization;

    /**
     * Authorize any action on this given policy if the user
     * is a super admin.
     */
    public function before($user, $ability)
    {
        /** Note that this will run before any other checks. This means is we return true we will be authorized
         *  for every action. However be aware that if we return false here, then we are also not authorizing 
         *  all other methods. We must be careful here, we only return true if the user is a "Super Admin" 
         *  but nothing is they are not, since we want other methods to run their own local checks. 
         * 
        */
        if($user->isSuperAdmin()) return true;
    }
    
    /**
     * Determine whether the user can view all locations.
     *
     * @param  \App\User $user
     * @param  \App\Location $model
     * @return mixed
     */
    public function viewAll(User $location)
    {
        //  Only the Super Admin can view all locations
        return $user->isSuperAdmin();
    }

    /**
     * Determine whether the user can view the location.
     *
     * @param  \App\User $user
     * @param  \App\Location $location
     * @return mixed
     */
    public function view(User $user, Location $location)
    {
        //  Only an Admin or Editor can view this location
        return $location->project()->isAdmin($user->id)  ||
               $location->project()->isEditor($user->id);
    }

    /**
     * Determine whether the user can create locations.
     *
     * @param  \App\User $user
     * @return mixed
     */
    public function create(User $user)
    {
        //  Any Authenticated user can create a locations
        return true;
    }

    /**
     * Determine whether the user can update the location.
     *
     * @param  \App\User $user
     * @param  \App\Location $location
     * @return mixed
     */
    public function update(User $user, Location $location)
    {
        //  Only an Admin, Editor can update this location
        return  $location->project()->isAdmin($user->id)  ||
                $location->project()->isEditor($user->id);
    }

    /**
     * Determine whether the user can delete the location.
     *
     * @param  \App\User $user
     * @param  \App\Location $location
     * @return mixed
     */
    public function delete(User $user, Location $location)
    {
        //  Only an Admin can delete this location
        return $location->project()->isAdmin($user->id);
    }

    /**
     * Determine whether the user can restore the location.
     *
     * @param  \App\User $user
     * @param  \App\Location $location
     * @return mixed
     */
    public function restore(User $user, Location $location)
    {
        //  Only an Admin can restore this location
        return $location->project()->isAdmin($user->id);
    }

    /**
     * Determine whether the user can permanently delete the location.
     *
     * @param  \App\User $user
     * @param  \App\Location $location
     * @return mixed
     */
    public function forceDelete(User $user, Location $location)
    {
        //  Only an Admin can force delete this location
        return $location->project()->isAdmin($user->id);
    }
}
