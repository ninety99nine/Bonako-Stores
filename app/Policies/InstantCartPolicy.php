<?php

namespace App\Policies;

use App\User;
use App\InstantCart;
use Illuminate\Auth\Access\HandlesAuthorization;

class InstantCartPolicy
{
    use HandlesAuthorization;

    /**
     * Authorize any action on this given policy if the user
     * is a super admin.
     */
    public function before($user, $ability)
    {
        try {

            /** Note that this will run before any other checks. This means is we return true we will be authorized
             *  for every action. However be aware that if we return false here, then we are also not authorizing
             *  all other methods. We must be careful here, we only return true if the user is a "Super Admin"
             *  but nothing is they are not, since we want other methods to run their own local checks.
             */
            if($user->isSuperAdmin()) return true;

        } catch (\Exception $e) {

            throw($e);

        }
    }

    /**
     * Determine whether the user can view all instant carts.
     *
     * @param  \App\User $user
     * @return mixed
     */
    public function viewAll(User $user)
    {
        try {

            //  Only the Super Admin can view all instant carts
            return $user->isSuperAdmin();

        } catch (\Exception $e) {

            throw($e);

        }
    }

    /**
     * Determine whether the user can view the instant cart.
     *
     * @param  \App\User $user
     * @param  \App\InstantCart $instant_cart
     * @return mixed
     */
    public function view(User $user, InstantCart $instant_cart)
    {
        try {

            //  Any Authenticated user can view this instant cart
            return auth('api')->user() ? true : false;

        } catch (\Exception $e) {

            throw($e);

        }
    }

    /**
     * Determine whether the user can create instant carts.
     *
     * @param  \App\User $user
     * @return mixed
     */
    public function create(User $user)
    {
        try {

            //  Any Authenticated user can create a instant cart
            return ($user instanceof \App\User) ? true : false;

        } catch (\Exception $e) {

            throw($e);

        }
    }

    /**
     * Determine whether the user can update the instant cart.
     *
     * @param  \App\User $user
     * @param  \App\InstantCart $instant_cart
     * @return mixed
     */
    public function update(User $user, InstantCart $instant_cart)
    {
        try {

            //  Only an Admin can update this instant cart
            return $instant_cart->location->isAdmin($user->id);

        } catch (\Exception $e) {

            throw($e);

        }
    }

    /**
     * Determine whether the user can delete the instant cart.
     *
     * @param  \App\User $user
     * @param  \App\InstantCart $instant_cart
     * @return mixed
     */
    public function delete(User $user, InstantCart $instant_cart)
    {
        try {

            //  Only an Admin can delete this instant cart
            return $instant_cart->location->isAdmin($user->id);

        } catch (\Exception $e) {

            throw($e);

        }
    }

    /**
     * Determine whether the user can restore the instant cart.
     *
     * @param  \App\User $user
     * @param  \App\InstantCart $instant_cart
     * @return mixed
     */
    public function restore(User $user, InstantCart $instant_cart)
    {
        try {

            //  Only an Admin can restore this instant cart
            return $instant_cart->location->isAdmin($user->id);

        } catch (\Exception $e) {

            throw($e);

        }
    }

    /**
     * Determine whether the user can permanently delete the instant cart.
     *
     * @param  \App\User $user
     * @param  \App\InstantCart $instant_cart
     * @return mixed
     */
    public function forceDelete(User $user, InstantCart $instant_cart)
    {
        try {

            //  Only an Admin can force delete this instant cart
            return $instant_cart->location->isAdmin($user->id);

        } catch (\Exception $e) {

            throw($e);

        }
    }
}
