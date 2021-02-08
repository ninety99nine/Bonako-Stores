<?php

namespace App\Policies;

use App\User;
use App\Order;
use Illuminate\Auth\Access\HandlesAuthorization;

class OrderPolicy
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
     * Determine whether the user can view all orders.
     *
     * @param  \App\User $user
     * @return mixed
     */
    public function viewAll(User $user)
    {
        try {

            //  Only the Super Admin can view all orders
            return $user->isSuperAdmin();

        } catch (\Exception $e) {

            throw($e);

        }

    }

    /**
     * Determine whether the user can view the order.
     *
     * @param  \App\User $user
     * @param  \App\Order $order
     * @return mixed
     */
    public function view(User $user, Order $order)
    {
        try {

            //  Any Authenticated user can view this order
            return auth('api')->user() ? true : false;

        } catch (\Exception $e) {

            throw($e);

        }
    }

    /**
     * Determine whether the user can create orders.
     *
     * @param  \App\User $user
     * @return mixed
     */
    public function create(User $user)
    {
        try {

            //  Any Authenticated user can create a order
            return ($user instanceof \App\User) ? true : false;

        } catch (\Exception $e) {

            throw($e);

        }
    }

    /**
     * Determine whether the user can fulfil the order.
     *
     * @param  \App\User  $user
     * @param  \App\Order  $order
     * @return mixed
     */
    public function fulfil(User $user, Order $order)
    {
        try {

            //  Only an Admin can fulfil this order
            return $order->location->isAdmin($user->id);

        } catch (\Exception $e) {

            throw($e);

        }
    }

    /**
     * Determine whether the user can cancel the order.
     *
     * @param  \App\User  $user
     * @param  \App\Order  $order
     * @return mixed
     */
    public function cancel(User $user, Order $order)
    {
        try {
            //  Only an Admin can cancel this order
            return $order->location->isAdmin($user->id);

        } catch (\Exception $e) {

            throw($e);

        }
    }

    /**
     * Determine whether the user can update the order.
     *
     * @param  \App\User $user
     * @param  \App\Order $order
     * @return mixed
     */
    public function update(User $user, Order $order)
    {
        try {

            //  Only an Admin can update this order
            return $order->location->isAdmin($user->id);

        } catch (\Exception $e) {

            throw($e);

        }
    }

    /**
     * Determine whether the user can delete the order.
     *
     * @param  \App\User $user
     * @param  \App\Order $order
     * @return mixed
     */
    public function delete(User $user, Order $order)
    {
        try {

            //  Only an Admin can delete this order
            return $order->location->isAdmin($user->id);

        } catch (\Exception $e) {

            throw($e);

        }
    }

    /**
     * Determine whether the user can restore the order.
     *
     * @param  \App\User $user
     * @param  \App\Order $order
     * @return mixed
     */
    public function restore(User $user, Order $order)
    {
        try {

            //  Only an Admin can restore this order
            return $order->location->isAdmin($user->id);

        } catch (\Exception $e) {

            throw($e);

        }
    }

    /**
     * Determine whether the user can permanently delete the order.
     *
     * @param  \App\User $user
     * @param  \App\Order $order
     * @return mixed
     */
    public function forceDelete(User $user, Order $order)
    {
        try {

            //  Only an Admin can force delete this order
            return $order->location->isAdmin($user->id);

        } catch (\Exception $e) {

            throw($e);

        }
    }
}
