<?php

namespace App\Policies;

use App\User;
use App\Store;
use Illuminate\Auth\Access\HandlesAuthorization;

class StorePolicy
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
     * Determine whether the user can view all stores.
     *
     * @param  \App\User $user
     * @return mixed
     */
    public function viewAll(User $user)
    {
        //  Only the Super Admin can view all stores
        return $user->isSuperAdmin();
    }

    /**
     * Determine whether the user can view the store.
     *
     * @param  \App\User $user
     * @param  \App\Store $store
     * @return mixed
     */
    public function view(User $user, Store $store)
    {
        //  Only an Admin can view this store
        return $store->isAdmin($user->id);
    }

    /**
     * Determine whether the user can create stores.
     *
     * @param  \App\User $user
     * @return mixed
     */
    public function create(User $user)
    {
        //  Any Authenticated user can create a instant carts
        return auth('api')->user() ? true : false;
    }

    /**
     * Determine whether the user can update the store.
     *
     * @param  \App\User $user
     * @param  \App\Store $store
     * @return mixed
     */
    public function update(User $user, Store $store)
    {
        //  Only an Admin can update this store
        return $store->isAdmin($user->id);
    }

    /**
     * Determine whether the user can delete the store.
     *
     * @param  \App\User $user
     * @param  \App\Store $store
     * @return mixed
     */
    public function delete(User $user, Store $store)
    {
        //  Only an Admin can delete this store
        return $store->isAdmin($user->id);
    }

    /**
     * Determine whether the user can restore the store.
     *
     * @param  \App\User $user
     * @param  \App\Store $store
     * @return mixed
     */
    public function restore(User $user, Store $store)
    {
        //  Only an Admin can restore this store
        return $store->isAdmin($user->id);
    }

    /**
     * Determine whether the user can permanently delete the store.
     *
     * @param  \App\User $user
     * @param  \App\Store $store
     * @return mixed
     */
    public function forceDelete(User $user, Store $store)
    {
        //  Only an Admin can force delete this store
        return $store->isAdmin($user->id);
    }
}
