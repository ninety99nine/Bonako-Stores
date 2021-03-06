<?php

namespace App\Traits;

use Carbon\Carbon;
use App\InstantCart;
use App\Http\Resources\InstantCart as InstantCartResource;
use App\Http\Resources\InstantCarts as InstantCartsResource;

trait InstantCartTraits
{
    public $request = null;
    public $instant_cart = null;

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
                return new InstantCartsResource($collection);

            // If this instance is not a collection
            }elseif($this instanceof \App\InstantCart){

                //  Transform the single instance
                return new InstantCartResource($this);

            }else{

                return $collection ?? $this;

            }

        } catch (\Exception $e) {

            throw($e);

        }
    }

    /**
     *  This method creates a new instant cart
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

            /**
             *  Create a new resource
             */
            $this->instant_cart = $this->create($template)->fresh();

            //  If created successfully
            if ( $this->instant_cart ) {

                //  Create a new cart resource
                $this->instant_cart->createResourceCart($data);

                //  Generate a payment shortcode (So that we can pay for the istant cart via USSD)
                $this->instant_cart->generateResourcePaymentShortCode($user);

            }

            //  Return a fresh instance
            return $this->instant_cart->fresh();

        } catch (\Exception $e) {

            throw($e);

        }

    }

    /**
     *  This method returns a list of instant carts
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

                //  Set the instant carts to this eloquent builder
                $instant_carts = $builder;

            }else{

                //  Get the instant carts
                $instant_carts = \App\InstantCart::latest();

            }

            //  Filter the instant carts
            $instant_carts = $this->filterResources($data, $instant_carts);

            //  Return instant carts
            return $this->collectionResponse($data, $instant_carts, $paginate, $convert_to_api_format);

        } catch (\Exception $e) {

            throw($e);

        }
    }

    /**
     *  This method filters the instant carts by search or status
     */
    public function filterResources($data = [], $instant_carts)
    {
        //  If we need to search for specific instant carts
        if ( isset($data['search']) && !empty($data['search']) ) {

            $instant_carts = $this->filterResourcesBySearch($data, $instant_carts);

        }elseif ( isset($data['status']) && !empty($data['status']) ) {

            $instant_carts = $this->filterResourcesByStatus($data, $instant_carts);

        }

        //  Return the instant carts
        return $instant_carts;
    }

    /**
     *  This method filters the instant carts by search
     */
    public function filterResourcesBySearch($data = [], $instant_carts)
    {
        //  Set the search term e.g "Chicken Express"
        $search_term = $data['search'] ?? null;

        //  Return searched instant carts otherwise original instant carts
        return empty($search_term) ? $instant_carts : $instant_carts->search($search_term);

    }

    /**
     *  This method filters the instant carts by status
     */
    public function filterResourcesByStatus($data = [], $instant_carts)
    {
        //  Set the statuses to an empty array
        $statuses = [];

        //  Set the status filters e.g ["active", "inactive", "expired", ...] or "active,inactive,expired, ..."
        $status_filters = $data['status'] ?? $data;

        //  If the filters are provided as String format e.g "active,inactive,expired"
        if( is_string($status_filters) ){

            //  Set the statuses to the exploded Array ["active", "inactive", "expired"]
            $statuses = explode(',', $status_filters);

        }elseif( is_array($status_filters) ){

            //  Set the statuses to the given Array ["active", "inactive", "expired"]
            $statuses = $status_filters;

        }

        //  Clean-up each status filter
        foreach ($statuses as $key => $status) {

            //  Convert " unpaid " to "Unpaid"
            $statuses[$key] = ucfirst(strtolower(trim($status)));
        }

        if ( $instant_carts && count($statuses) ) {

            if( in_array('Active', $statuses) ){

                $instant_carts = $instant_carts->active();

            }elseif( in_array('Inactive', $statuses) ){

                $instant_carts = $instant_carts->inActive();

            }

            if( in_array('Expired', $statuses) ){

                $instant_carts = $instant_carts->expired();

            }

            if( in_array('Free delivery', $statuses) ){

                $instant_carts = $instant_carts->offersFreeDelivery();

            }

        }

        //  Return the instant carts
        return $instant_carts;
    }

    /**
     *  This method returns a single instant cart
     */
    public function getResource($id)
    {
        try {

            //  Get the resource
            $instant_cart = \App\InstantCart::where('id', $id)->first() ?? null;

            //  If exists
            if ($instant_cart) {

                //  Return instant cart
                return $instant_cart;

            } else {

                //  Return "Not Found" Error
                return help_resource_not_found();

            }

        } catch (\Exception $e) {

            throw($e);

        }
    }

    /**
     *  This method returns a single instant cart location
     */
    public function getResourceLocation()
    {
        try {

            //  Get the resource
            $location = $this->location;

            //  If exists
            if ($location) {

                //  Return location
                return $location;

            } else {

                //  Return "Not Found" Error
                return help_resource_not_found();

            }

        } catch (\Exception $e) {

            throw($e);

        }
    }

    /**
     *  This method deletes a single instant cart
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
     *  This method creates a new instant cart
     */
    public function createResourceCart($data = [])
    {
        try {

            //  Set the cart owning model
            $model = $this;

            /**
             *  Create new a cart resource
             */
            $cart = ( new \App\Cart() )->createResource($data, $model);

            //  Return the cart resource
            return $cart;

        } catch (\Exception $e) {

            throw($e);

        }
    }

    /**
     *  This method generates a instant cart subscription
     */
    public function generateResourceSubscription($data = [], $user)
    {
        try {

            //  Extract the Request Object data (CommanTraits)
            $data = $this->extractRequestData($data);

            //  Set the sms owning model
            $model = $this;

            //  Create a new subscription
            $subscription = ( new \App\Subscription() )->createResource($data, $model, $user);

            //  Generate visit short code (CommonTraits)
            $this->generateResourceVisitShortCode($user);

            //  Expire payment short codes (CommonTraits)
            $this->expirePaymentShortCode();

            //  Load the visit short code
            $this->load('visitShortCode');

            //  Send the payment request sms
            $this->sendSubscriptionSuccessSms($user);

            //  Return the new subscription
            return $subscription;

        } catch (\Exception $e) {

            throw($e);

        }
    }

    /**
     *  This method sends the subscription success message to the merchant
     */
    public function sendSubscriptionSuccessSms($user = null)
    {
        try {

            //  Set the instant cart visit short code
            $visit_short_code = $this->visitShortCode;

            //  If we have the visit short code
            if( $visit_short_code ){

                //  Set expiry to the same time as the subscription end datetime
                $expiry_date = Carbon::parse($visit_short_code->expires_at)->format('d/m/Y H:i');

                //  Craft the sms message
                $message = 'Subscription for "'.$this->name.'" successful. Dial '.$visit_short_code->dialing_code.
                           ' to use your instant cart. Expires on '.$expiry_date;

                $type = 'instant cart subscription confirmation';

                $data = [

                    //  Set the type on the data
                    'type' => $type,

                    //  Set the message on the data
                    'message' => $message,

                    //  Set the mobile_number on the data
                    'mobile_number' => $user->mobile_number

                ];

                //  Set the sms owning model
                $model = $this;

                //  Create a new sms and send
                return ( new \App\Sms() )->createResource($data, $model, $user)->sendSms();

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
                if ($user->can('create', InstantCart::class) === false) {

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
