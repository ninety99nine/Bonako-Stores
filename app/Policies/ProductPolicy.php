<?php

namespace App\Policies;

use App\User;
use App\Product;
use Illuminate\Auth\Access\HandlesAuthorization;

class ProductPolicy
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
     * Determine whether the user can view all products.
     *
     * @param  \App\User $user
     * @return mixed
     */
    public function viewAll(User $user)
    {
        //  Only the Super Admin can view all products
        return $user->isSuperAdmin();
    }

    /**
     * Determine whether the user can view the product.
     *
     * @param  \App\User $user
     * @param  \App\Product $product
     * @return mixed
     */
    public function view(User $user, Product $product)
    {
        //  Only an Admin or Editor can view this product
        return $product->store()->isAdmin($user->id)  ||
               $product->store()->isEditor($user->id);
    }

    /**
     * Determine whether the user can create products.
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
     * Determine whether the user can update the product.
     *
     * @param  \App\User $user
     * @param  \App\Product $product
     * @return mixed
     */
    public function update(User $user, Product $product)
    {
        //  Only an Admin, Editor can update this product
        return  $product->store()->isAdmin($user->id)  ||
                $product->store()->isEditor($user->id);
    }

    /**
     * Determine whether the user can delete the product.
     *
     * @param  \App\User $user
     * @param  \App\Product $product
     * @return mixed
     */
    public function delete(User $user, Product $product)
    {
        //  Only an Admin can delete this product
        return $product->store()->isAdmin($user->id);
    }

    /**
     * Determine whether the user can restore the product.
     *
     * @param  \App\User $user
     * @param  \App\Product $product
     * @return mixed
     */
    public function restore(User $user, Product $product)
    {
        //  Only an Admin can restore this product
        return $product->store()->isAdmin($user->id);
    }

    /**
     * Determine whether the user can permanently delete the product.
     *
     * @param  \App\User $user
     * @param  \App\Product $product
     * @return mixed
     */
    public function forceDelete(User $user, Product $product)
    {
        //  Only an Admin can force delete this product
        return $product->store()->isAdmin($user->id);
    }
}
