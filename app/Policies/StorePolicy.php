<?php

namespace App\Policies;

use App\Store;
use App\User;
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
     * Determine whether the user can view all stores.
     *
     * @return mixed
     */
    public function viewAll(User $user)
    {
        try {

            //  Only the Super Admin can view all stores
            return $user->isSuperAdmin();

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

            //  Only an Admin can view this store
            return $store->isAdmin($user->id);

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
            return auth('api')->user() ? true : false;

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

            //  Only an Admin can update this store
            return $store->isAdmin($user->id);

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

            //  Only an Admin can delete this store
            return $store->isAdmin($user->id);

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

            //  Only an Admin can restore this store
            return $store->isAdmin($user->id);

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

            //  Only an Admin can force delete this store
            return $store->isAdmin($user->id);

        } catch (\Exception $e) {

            throw($e);

        }
    }
}
