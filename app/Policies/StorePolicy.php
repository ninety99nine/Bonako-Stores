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
        try {

            /* Note that this will run before any other checks. This means is we return true we will be authorized
            *  for every action. However be aware that if we return false here, then we are also not authorizing
            *  all other methods. We must be careful here, we only return true if the user is a "Super Admin"
            *  but nothing is they are not, since we want other methods to run their own local checks.
            */
            if ($user->isSuperAdmin()) {
                return true;
            }

        } catch (\Exception $e) {

            throw($e);

        }
    }

    /**
     * Determine whether the user can view the store.
     *
     * @return mixed
     */
    public function view(User $user, Store $store)
    {
        try {

            //  Any Authenticated user can view this store
            return auth('api')->user() ? true : false;

        } catch (\Exception $e) {

            throw($e);

        }
    }

    /**
     * Determine whether the user can create stores.
     *
     * @return mixed
     */
    public function create(User $user)
    {
        try {

            //  Any Authenticated user can create a store
            return ($user instanceof \App\User) ? true : false;

        } catch (\Exception $e) {

            throw($e);

        }
    }

    /**
     * Determine whether the user can update the store.
     *
     * @return mixed
     */
    public function update(User $user, Store $store)
    {
        try {

            //  Only the Owner with a store subscription can update this store
            return $store->isOwner($user) && $store->isSubscribed($user);

        } catch (\Exception $e) {

            throw($e);

        }
    }

    /**
     * Determine whether the user can subscribe to the store.
     *
     * @return mixed
     */
    public function subscribe(User $user, Store $store)
    {
        try {

            //  Only the Owner can subscribe this store
            return $store->isOwner($user);

        } catch (\Exception $e) {

            throw($e);

        }
    }

    /**
     * Determine whether the user can delete the store.
     *
     * @return mixed
     */
    public function delete(User $user, Store $store)
    {
        try {

            //  Only the Owner with a store subscription can delete this store
            return $store->isOwner($user) && $store->isSubscribed($user);

        } catch (\Exception $e) {

            throw($e);

        }
    }

    /**
     * Determine whether the user can restore the store.
     *
     * @return mixed
     */
    public function restore(User $user, Store $store)
    {
        try {

            //  Only the Owner can restore this store
            return $store->isOwner($user);

        } catch (\Exception $e) {

            throw($e);

        }
    }

    /**
     * Determine whether the user can permanently delete the store.
     *
     * @return mixed
     */
    public function forceDelete(User $user, Store $store)
    {
        try {

            //  Only the Owner can force delete this store
            return $store->isOwner($user);

        } catch (\Exception $e) {

            throw($e);

        }
    }
}
