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
        try {

            if($user->isSuperAdmin()) return true;

        } catch (\Exception $e) {

            throw($e);

        }
    }

    /**
     * Determine whether the user can view all products.
     *
     * @param  \App\User $user
     * @return mixed
     */
    public function viewAll(User $user)
    {
        try {

            //  Only the Super Admin can view all products
            return $user->isSuperAdmin();

        } catch (\Exception $e) {

            throw($e);

        }
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
        try {

            //  Any Authenticated user can view this product
            return auth('api')->user() ? true : false;

        } catch (\Exception $e) {

            throw($e);

        }
    }

    /**
     * Determine whether the user can create products.
     *
     * @param  \App\User $user
     * @return mixed
     */
    public function create(User $user)
    {
        try {

            //  Any Authenticated user can create a product
            return ($user instanceof \App\User) ? true : false;

        } catch (\Exception $e) {

            throw($e);

        }
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
        try {

            //  Only an Admin can update this product
            return $product->isAdmin($user);

        } catch (\Exception $e) {

            throw($e);

        }
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
        try {

            //  Only an Admin can delete this product
            return $product->isAdmin($user);

        } catch (\Exception $e) {

            throw($e);

        }
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
        try {

            //  Only an Admin can restore this product
            return $product->isAdmin($user);

        } catch (\Exception $e) {

            throw($e);

        }
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
        try {

            //  Only an Admin can force delete this product
            return $product->isAdmin($user);

        } catch (\Exception $e) {

            throw($e);

        }
    }
}
