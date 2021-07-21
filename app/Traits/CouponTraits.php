<?php

namespace App\Traits;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Validation\ValidationException;
use App\Http\Resources\Coupon as CouponResource;
use App\Http\Resources\Coupons as CouponsResource;

trait CouponTraits
{
    public $coupon = null;
    public $request = null;

    /**
     *  This method transforms a collection or single model instance
     */
    public function convertToApiFormat($collection = null)
    {
        try {

            // If this instance is a collection or a paginated collection
            if( $collection instanceof \Illuminate\Support\Collection ||
                $collection instanceof \Illuminate\Pagination\LengthAwarePaginator ){

                //  Transform the multiple instances
                return new CouponsResource($collection);

            // If this instance is not a collection
            }elseif($this instanceof \App\Coupon){

                //  Transform the single instance
                return new CouponResource($this);

            }else{

                return $collection ?? $this;

            }

        } catch (\Exception $e) {

            throw($e);

        }
    }

    /**
     *  This method creates a new coupon
     */
    public function createResource($data = [], $user = null)
    {
        try {

            //  Extract the Request Object data (CommanTraits)
            $data = $this->extractRequestData($data);

            //  Validate the data
            $this->createResourceValidation($data);

            //  Verify permissions
            $this->createResourcePermission($user);

            //  Set the template with the resource fields allowed
            $template = collect($data)->only($this->getFillable())->toArray();

            //  If the current authenticated user is a Super Admin and the "user_id" is provided
            if( auth('api')->user()->isSuperAdmin() && isset($data['user_id']) ){

                //  Set the "user_id" provided as the user responsible for owning this resource
                $template['user_id'] = $data['user_id'];

            }else{

                //  Set the current authenticated user as the user responsible for owning this resource
                $template['user_id'] = auth('api')->user()->id;

            }

            /**
             *  Create a new resource
             */
            $this->coupon = $this->create($template);

            //  If created successfully
            if ( $this->coupon ) {

                //  Generate the resource creation report
                $this->coupon->generateResourceCreationReport();

                //  Return the coupon
                return $this->coupon;

            }

        } catch (\Exception $e) {

            throw($e);

        }

    }

    /**
     *  This method generates a coupon creation report
     */
    public function generateResourceCreationReport()
    {
        //  Get the store with locations holding this coupon
        $store = \App\Store::with('locations')->whereHas('locations', function (Builder $query) {
            $query->whereHas('coupons', function (Builder $query) {
                $query->where('coupons.id', $this->id);
            });
        })->first();

        //  Foreach store location
        foreach( $store->locations as $location ){

            //  Generate the resource creation report
            ( new \App\Report() )->generateResourceCreationReport($this, [
                'name' => $this->name,
                'activation_type' => $this->activation_type['type'],
                'apply_discount' => $this->apply_discount['status'],
                'discount_rate_type' => $this->discount_rate_type['type'],
                'fixed_rate' => $this->fixed_rate['amount'],
                'percentage_rate' => $this->percentage_rate,
                'allow_free_delivery' => $this->allow_free_delivery['status'],
            ], $store->id, $location->id);

        }
    }

    /**
     *  This method updates an existing coupon
     */
    public function updateResource($data = [], $user = null)
    {
        try {

            //  Extract the Request Object data (CommanTraits)
            $data = $this->extractRequestData($data);

            //  Merge the existing data with the new data
            $data = array_merge(collect($this)->only($this->getFillable())->toArray(), $data);

            //  Verify permissions
            $this->updateResourcePermission($user);

            //  Validate the data
            $this->updateResourceValidation($data);

            //  Set the template with the resource fields allowed
            $template = collect($data)->only($this->getFillable())->toArray();

            //  Set the original user as the primary user responsible for creating this resource
            $template['user_id'] = $this->user_id;

            /**
             *  Update the resource details
             */
            $updated = $this->update($template);

            //  If updated successfully
            if ($updated) {

                //  Return a fresh instance
                return $this->fresh();

            }else{

                //  Return original instance
                return $this;

            }

        } catch (\Exception $e) {

            throw($e);

        }
    }

    /**
     *  This method deletes a single coupon
     */
    public function deleteResource($user = null)
    {
        try {

            //  Verify permissions
            $this->forceDeleteResourcePermission($user);

            /**
             *  Delete the resource
             */
            return $this->delete();


        } catch (\Exception $e) {

            throw($e);

        }
    }

    /**
     *  This method returns a list of coupons
     */
    public function getResources($data = [], $builder = null, $paginate = true, $convert_to_api_format = true)
    {
        try {

            //  Extract the Request Object data (CommanTraits)
            $data = $this->extractRequestData($data);

            //  Validate the data (CommanTraits)
            $this->getResourcesValidation($data);

            //  If we already have an eloquent builder defined
            if( is_object($builder) ){

                //  Set the coupons to this eloquent builder
                $coupons = $builder;

            }else{

                //  Get the coupons
                $coupons = \App\Coupon::latest();

            }

            //  Filter the coupons
            $coupons = $this->filterResources($data, $coupons);

            //  Return coupons
            return $this->collectionResponse($data, $coupons, $paginate, $convert_to_api_format);

        } catch (\Exception $e) {

            throw($e);

        }
    }

    /**
     *  This method returns a list of coupon totals
     *
     *  Note: $builder is an instance of the eloquent builder. In this
     *  case the eloquent builder must represent an instance of coupons
     */
    public function getResourceTotals($data = [], $builder)
    {
        try {

            //  Extract the Request Object data (CommanTraits)
            $data = $this->extractRequestData($data);

            //  Set the totals
            $totals = [
                'statuses' => [],
                'total' => $builder->count()
            ];

            //  Set the status filters to calculate the totals
            $filters = [
                'active', 'inactive', 'free delivery'
            ];

            collect($filters)->map(function($filter) use (&$totals, $builder){

                /**
                 *  $filter = 'active' or 'inactive' ... e.t.c
                 *
                 *  $bulder = Eloquent Builder Instance e.g $location->instantCarts()->latest()
                 *
                 *  We clone the builder object to have a new instance to use when filtering the coupons.
                 *  If we do not clone, only one object instance will be used for every filter producing
                 *  incorrect results e.g The instance may be used to filter only coupons with a
                 *  status of "active" and return a few results. The same builder will then be used to
                 *  filter coupons with a status of "inactive", however since we are using the
                 *  same instance it would have applied the previous filter of "active", which means
                 *  that the final coupons returned will need to be "active" and "inactive".
                 *  This gets worse as we load more filters e.g It will look to return instant
                 *  carts that must match every status i.e "active", "inactive", "expired",
                 *  e.t.c
                 */
                $totals['statuses'][$filter] = $this->filterResources(['status' => $filter], clone $builder)->count();

            })->toArray();

            /**
             *  Return the totals
             *
             *  Example result
             *
             *  [
             *    "statuses" => [
             *       "active" => 1,
             *       "inactive" => 0,
             *       "free delivery" => 0
             *      ]
             *  ]
             */
            return $totals;

        } catch (\Exception $e) {

            throw($e);

        }
    }

    /**
     *  This method filters the coupons by search or status
     */
    public function filterResources($data = [], $coupons)
    {
        //  If we need to search for specific coupons
        if ( isset($data['search']) && !empty($data['search']) ) {

            $coupons = $this->filterResourcesBySearch($data, $coupons);

        }elseif ( isset($data['status']) && !empty($data['status']) ) {

            $coupons = $this->filterResourcesByStatus($data, $coupons);

        }

        //  Return the coupons
        return $coupons;
    }

    /**
     *  This method filters the coupons by search
     */
    public function filterResourcesBySearch($data = [], $coupons)
    {
        //  Set the search term e.g "Chicken Express"
        $search_term = $data['search'] ?? null;

        //  Return searched coupons otherwise original coupons
        return empty($search_term) ? $coupons : $coupons->search($search_term);

    }

    /**
     *  This method filters the coupons by status
     */
    public function filterResourcesByStatus($data = [], $coupons)
    {
        //  Set the statuses to an empty array
        $statuses = [];

        //  Set the status filters e.g ["active", "inactive", ...] or "active,inactive, ..."
        $status_filters = $data['status'] ?? $data;

        //  If the filters are provided as String format e.g "active,inactive"
        if( is_string($status_filters) ){

            //  Set the statuses to the exploded Array ["active", "inactive"]
            $statuses = explode(',', $status_filters);

        }elseif( is_array($status_filters) ){

            //  Set the statuses to the given Array ["active", "inactive"]
            $statuses = $status_filters;

        }

        //  Clean-up each status filter
        foreach ($statuses as $key => $status) {

            //  Convert " active " to "Active"
            $statuses[$key] = ucfirst(strtolower(trim($status)));
        }

        if ( $coupons && count($statuses) ) {

            if( in_array('Active', $statuses) ){

                $coupons = $coupons->active();

            }elseif( in_array('Inactive', $statuses) ){

                $coupons = $coupons->inActive();

            }

            if( in_array('Free delivery', $statuses) ){

                $coupons = $coupons->offersFreeDelivery();

            }

        }

        //  Return the coupons
        return $coupons;
    }

    /**
     *  This method returns a single coupon
     */
    public function getResource($id)
    {
        try {

            //  Get the resource
            $coupon = \App\Coupon::where('id', $id)->first() ?? null;

            //  If exists
            if ($coupon) {

                //  Return coupon
                return $coupon;

            } else {

                //  Return "Not Found" Error
                return help_resource_not_found();

            }

        } catch (\Exception $e) {

            throw($e);

        }
    }

    /**
     *  This method checks permissions for creating a new resource
     */
    public function createResourcePermission($user = null)
    {
        try {

            //  If the user is provided
            if( $user ){

                //  Check if the user is authourized to create the resource
                if ($user->can('create', Coupon::class) === false) {

                    //  Return "Not Authourized" Error
                    return help_not_authorized();

                }

            }

        } catch (\Exception $e) {

            throw($e);

        }
    }

    /**
     *  This method checks permissions for updating an existing resource
     */
    public function updateResourcePermission($user = null)
    {
        try {

            //  If the user is provided
            if( $user ){

                //  Check if the user is authourized to update the resource
                if ($user->can('update', $this)) {

                    //  Return "Not Authourized" Error
                    return help_not_authorized();

                }

            }

        } catch (\Exception $e) {

            throw($e);

        }
    }

    /**
     *  This method checks permissions for deleting an existing resource
     */
    public function forceDeleteResourcePermission($user = null)
    {
        try {

            //  If the user is provided
            if( $user ){

                //  Check if the user is authourized to delete the resource
                if ($user->can('forceDelete', $this)) {

                    //  Return "Not Authourized" Error
                    return help_not_authorized();

                }

            }

        } catch (\Exception $e) {

            throw($e);

        }
    }

    /**
     *  This method validates creating a new resource
     */
    public function createResourceValidation($data = [])
    {
        try {

            //  Set validation rules
            $rules = [

            ];

            //  Set validation messages
            $messages = [

            ];

            //  Method executed within CommonTraits
            $this->resourceValidation($data, $rules, $messages);

        } catch (\Exception $e) {

            throw($e);

        }
    }

    /**
     *  This method validates updating an existing resource
     */
    public function updateResourceValidation($data = [])
    {
        try {

            //  Run the resource creation validation
            $this->createResourceValidation($data);

        } catch (\Exception $e) {

            throw($e);

        }

    }

}
