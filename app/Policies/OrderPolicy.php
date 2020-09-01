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
        /** Note that this will run before any other checks. This means is we return true we will be authorized
         *  for every action. However be aware that if we return false here, then we are also not authorizing 
         *  all other methods. We must be careful here, we only return true if the user is a "Super Admin" 
         *  but nothing is they are not, since we want other methods to run their own local checks. 
         * 
        */
        if($user->isSuperAdmin()) return true;
    }
    
    /**
     * Determine whether the user can view all orders.
     *
     * @param  \App\User $user
     * @param  \App\Order $model
     * @return mixed
     */
    public function viewAll(User $order)
    {
        //  Only the Super Admin can view all orders
        return $user->isSuperAdmin();
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
        //  Only an Admin or Editor can view this order
        return $order->store()->isAdmin($user->id)  ||
               $order->store()->isEditor($user->id);
    }

    /**
     * Determine whether the user can create orders.
     *
     * @param  \App\User $user
     * @return mixed
     */
    public function create(User $user)
    {
        //  Any Authenticated user can create a orders
        return true;
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
        //  Only an Admin, Editor can update this order
        return  $order->store()->isAdmin($user->id)  ||
                $order->store()->isEditor($user->id);
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
        //  Only an Admin can delete this order
        return $order->store()->isAdmin($user->id);
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
        //  Only an Admin can restore this order
        return $order->store()->isAdmin($user->id);
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
        //  Only an Admin can force delete this order
        return $order->store()->isAdmin($user->id);
    }
}
