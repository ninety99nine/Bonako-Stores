<?php

namespace App\Traits;

use Illuminate\Database\Eloquent\Builder;
use App\Http\Resources\Customer as CustomerResource;
use App\Http\Resources\Customers as CustomersResource;

trait CustomerTraits
{
    public $customer = null;
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
                return new CustomersResource($collection);

            // If this instance is not a collection
            }elseif($this instanceof \App\Customer){

                //  Transform the single instance
                return new CustomerResource($this);

            }else{

                return $collection ?? $this;

            }

        } catch (\Exception $e) {

            throw($e);

        }
    }

    /**
     *  This method creates a new customer
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

            //  Get the customer matching the given user id and location id
            $this->customer = \App\Customer::where('user_id', $data['user_id'])->where('location_id', $data['location_id'])->first();

            //  If the customer already exists for the given location
            if ( $this->customer ) {

                //  Update the existing customer
                return $this->customer->updateResource($data, $user);

            }else{

                /**
                 *  Create a new resource
                 */
                $this->customer = $this->create($template);

                //  If created successfully
                if ( $this->customer ) {

                    //  Generate the resource creation report
                    $this->customer->generateResourceCreationReport();

                    //  Return the customer
                    return $this->customer;

                }

            }

        } catch (\Exception $e) {

            throw($e);

        }

    }

    /**
     *  This method generates a customer creation report
     */
    public function generateResourceCreationReport()
    {
        //  Get the store with locations holding this customer
        $store = \App\Store::with('locations')->whereHas('locations', function (Builder $query) {
            $query->whereHas('customers', function (Builder $query) {
                $query->where('customers.id', $this->id);
            });
        })->first();

        //  Foreach store location
        foreach( $store->locations as $location ){

            //  Generate the resource creation report
            ( new \App\Report() )->generateResourceCreationReport($this, [], $store->id, $location->id);

        }
    }

    /**
     *  This method updates an existing customer
     */
    public function updateResource($data = [], $user = null)
    {
        try {

            //  Extract the Request Object data (CommanTraits)
            $data = $this->extractRequestData($data);

            //  Verify permissions
            $this->updateResourcePermission($user);

            //  Validate the data
            $this->updateResourceValidation($data);

            //  Set the template with the resource fields allowed
            $template = [

                //  Set the original user as the primary user for this resource
                'user_id' => $this->user_id,

                //  Set the original location as the primary location for this resource
                'location_id' => $this->location_id

            ];

            $exceptable_fields = collect( $this->getFillable() )->filter(function ($field) use ($template) {

                //  The field must not be contained in the template
                return in_array($field, collect($template)->keys()->all()) == false;

            });

            foreach($exceptable_fields as $field ){

                /**
                 *  Same as:
                 *
                 *  $template['total_coupons_used_on_checkout'] = isset($data['total_coupons_used_on_checkout']) ? ($this->total_coupons_used_on_checkout + $data['total_coupons_used_on_checkout']) : $this->total_coupons_used_on_checkout;
                 */
                $template[$field] = isset($data[$field]) ? ((int) $this->$field + (int) $data[$field]) : $this->$field;

            }

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
     *  This method deletes a single customer
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
     *  This method returns a list of customers
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

                //  Set the customers to this eloquent builder
                $customers = $builder;

            }else{

                //  Get the customers
                $customers = \App\Customer::latest();

            }

            //  Filter the customers
            $customers = $this->filterResources($data, $customers);

            //  Return customers
            return $this->collectionResponse($data, $customers, $paginate, $convert_to_api_format);

        } catch (\Exception $e) {

            throw($e);

        }
    }

    /**
     *  This method returns a list of customer totals
     *
     *  Note: $builder is an instance of the eloquent builder. In this
     *  case the eloquent builder must represent an instance of customers
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
                'has orders placed by customer', 'has orders placed by store',
                'used coupons', 'used instant carts', 'used adverts'
            ];

            collect($filters)->map(function($filter) use (&$totals, $builder){

                /**
                 *  $filter = 'used coupons' or 'used instant carts' ... e.t.c
                 *
                 *  $bulder = Eloquent Builder Instance e.g $location->customers()->latest()
                 *
                 *  We clone the builder object to have a new instance to use when filtering the customers.
                 *  If we do not clone, only one object instance will be used for every filter producing
                 *  incorrect results e.g The instance may be used to filter only customers with a status
                 *  of "used coupons" and return a few results. The same builder will then be used to
                 *  filter customers with a status of "used instant carts", however since we are using the
                 *  same instance it would have applied the previous filter of "used coupons", which means
                 *  that the final customers returned will need to be "used coupons" and "used instant carts".
                 *  This gets worse as we load more filters e.g It will look to return customers that must
                 *  match every status i.e "used coupons", "used instant carts", "used adverts",
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
     *  This method filters the customers by search or status
     */
    public function filterResources($data = [], $customers)
    {
        //  If we need to search for specific customers
        if ( isset($data['search']) && !empty($data['search']) ) {

            $customers = $this->filterResourcesBySearch($data, $customers);

        }elseif ( isset($data['status']) && !empty($data['status']) ) {

            $customers = $this->filterResourcesByStatus($data, $customers);

        }

        //  Return the customers
        return $customers;
    }

    /**
     *  This method filters the customers by search
     */
    public function filterResourcesBySearch($data = [], $customers)
    {
        //  Set the search term e.g "John"
        $search_term = $data['search'] ?? null;

        //  Return searched customers otherwise original customers
        return empty($search_term) ? $customers : $customers->search($search_term);

    }

    /**
     *  This method filters the customers by status
     */
    public function filterResourcesByStatus($data = [], $customers)
    {
        //  Set the statuses to an empty array
        $statuses = [];

        //  Set the status filters e.g ["used coupons", "used instant carts", ...] or "used coupons,used instant carts, ..."
        $status_filters = $data['status'] ?? $data;

        //  If the filters are provided as String format e.g "used coupons,used instant carts"
        if( is_string($status_filters) ){

            //  Set the statuses to the exploded Array ["used coupons", "used instant carts"]
            $statuses = explode(',', $status_filters);

        }elseif( is_array($status_filters) ){

            //  Set the statuses to the given Array ["used coupons", "used instant carts"]
            $statuses = $status_filters;

        }

        //  Clean-up each status filter
        foreach ($statuses as $key => $status) {

            //  Convert " used coupons " to "Used coupons"
            $statuses[$key] = ucfirst(strtolower(trim($status)));
        }

        if ( $customers && count($statuses) ) {

            if( in_array('Has orders placed by customer', $statuses) ){

                $customers = $customers->hasOrdersPlacedByCustomer();

            }

            if( in_array('Has orders placed by store', $statuses) ){

                $customers = $customers->hasOrdersPlacedByStore();

            }

            if( in_array('Has orders with free delivery', $statuses) ){

                $customers = $customers->hasOrdersWithFreeDelivery();


            if( in_array('used coupons', $statuses) ){

                $customers = $customers->hasUsedCoupons();

            }

            if( in_array('used instant carts', $statuses) ){

                $customers = $customers->hasUsedInstantCarts();

            }

            if( in_array('used adverts', $statuses) ){

                $customers = $customers->hasUsedAdverts();

            }
            }

        }

        //  Return the customers
        return $customers;
    }

    /**
     *  This method returns a single customer
     */
    public function getResource($id)
    {
        try {

            //  Get the resource
            $customer = \App\Customer::where('id', $id)->first() ?? null;

            //  If exists
            if ($customer) {

                //  Return customer
                return $customer;

            } else {

                //  Return "Not Found" Error
                return help_resource_not_found();

            }

        } catch (\Exception $e) {

            throw($e);

        }
    }

    /**
     *  This method returns a list of customer orders
     */
    public function getResourceOrders($data = [], $user, $paginate = true, $convert_to_api_format = true)
    {
        try {

            //  Filter the customer orders
            $orders = $this->orders();

            //  Return a list of customer orders
            return (new \App\Order())->getResources($data, $orders, $paginate, $convert_to_api_format);

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
                if ($user->can('create', Customer::class) === false) {

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
