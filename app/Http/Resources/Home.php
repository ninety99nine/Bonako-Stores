<?php

namespace App\Http\Resources;

use App\Http\Resources\User as UserResource;
use Illuminate\Http\Resources\Json\JsonResource;

class Home extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param \Illuminate\Http\Request $request
     *
     * @return array
     */
    public function toArray($request)
    {
        return [
            /*  Resource Links */
            '_links' => [
                'curies' => [
                    ['name' => 'sce', 'href' => 'https://oq-sce.co.bw/docs/rels/{rel}', 'templated' => true],
                ],

                //  Link to current resource
                'self' => [
                    'href' => url()->full(),
                    'title' => 'API Home - Your API starting point.',
                ],

                //  Link to login
                'bos:login' => [
                    'href' => route('login'),
                    'title' => 'Authenticate user',
                ],

                //  Link to register
                'bos:register' => [
                    'href' => route('register'),
                    'title' => 'Register new user',
                ],

                //  Link to generate mobile verification code
                'bos:generate_mobile_verification_code' => [
                    'href' => route('generate-mobile-verification-code'),
                    'title' => 'Generate mobile verification',
                ],

                //  Link to validate mobile verification code
                'bos:verify_mobile_verification_code' => [
                    'href' => route('verify-mobile-verification-code'),
                    'title' => 'Generate mobile verification',
                ],

                //  Link to validate mobile verification code
                'bos:show_mobile_verification_code' => [
                    'href' => route('show-mobile-verification-code'),
                    'title' => 'Show mobile verification',
                ],

                //  Link to check if account exists
                'bos:account_exists' => [
                    'href' => route('account-exists'),
                    'title' => 'Check if user account exists',
                ],

                //  Link to reset password
                'bos:reset_password' => [
                    'href' => route('reset-password'),
                    'title' => 'Reset the user\'s password',
                ],

                //  Link to logout from current device
                'bos:logout' => [
                    'href' => route('logout'),
                    'title' => 'Logout from current device',
                ],

                //  Link to logout from all devices
                'bos:logout_everyone' => [
                    'href' => route('logout', ['everyone' => 'true']),
                    'title' => 'Logout all devices',
                ],

                //  Link to stores resources (Used to create new store resource)
                'bos:stores' => [
                    'href' => route('stores'),
                    'title' => 'Get or create stores',
                ],

                //  Link to locations resources (Used to create new location resource)
                'bos:locations' => [
                    'href' => route('locations'),
                    'title' => 'Create locations',
                ],

                //  Link to orders resources (Used to create new order resource)
                'bos:orders' => [
                    'href' => route('orders'),
                    'title' => 'Create orders',
                ],

                //  Link to verify delivery confirmation code
                'bos:order_verify_delivery_confirmation_code' => [
                    'href' => route('order-verify-delivery-confirmation-code'),
                    'title' => 'Verify order delivery confirmation code',
                ],

                //  Link to products resources (Used to create new product resource)
                'bos:products' => [
                    'href' => route('products'),
                    'title' => 'Create products',
                ],

                //  Link to coupons resources (Used to create new coupon resource)
                'bos:coupons' => [
                    'href' => route('coupons'),
                    'title' => 'Create coupons',
                ],

                //  Link to carts resources (Used to create new cart resource)
                'bos:carts' => [
                    'href' => route('carts'),
                    'title' => 'Create carts',
                ],

                //  Link to products resources (Used to create new product resource)
                'bos:instant_carts' => [
                    'href' => route('instant-carts'),
                    'title' => 'Create instant carts',
                ],

                //  Link to adverts resources (Used to add a new advert resource)
                'bos:adverts' => [
                    'href' => route('adverts'),
                    'title' => 'Get or create an advert',
                ],

                //  Link to arrange adverts
                'bos:advert_arrangement' => [
                    'href' => route('advert-arrangement'),
                    'title' => 'POST to arrange adverts',
                ],

                //  Link to popular stores resources (Used to add a new popular store resource)
                'bos:popular_stores' => [
                    'href' => route('popular-stores'),
                    'title' => 'Get or create a popular store',
                ],

                //  Link to arrange popular stores
                'bos:popular_store_arrangement' => [
                    'href' => route('popular-store-arrangement'),
                    'title' => 'POST to arrange popular stores',
                ],

                //  Link to subscription plans resources (Used to get subscription plans)
                'bos:subscription_plans' => [
                    'href' => route('subscription-plans'),
                    'title' => 'Get subscription plans',
                ],

                //  Link to payment method resources (Used to get payment methods)
                'bos:payment_methods' => [
                    'href' => route('payment-methods'),
                    'title' => 'Get payment methods',
                ],

                //  Link to product types resources (Used to get product types)
                'bos:product_types' => [
                    'href' => route('product-types'),
                    'title' => 'Get product types',
                ],

                //  Link to address types resources (Used to get address types)
                'bos:address_types' => [
                    'href' => route('address-types'),
                    'title' => 'Get address types',
                ],

                //  Link to report statistics (Used to get performance statistics)
                'bos:report_statistics' => [
                    'href' => route('report-statistics'),
                    'title' => 'Get report statistics',
                ],

                //  Link to product types resources (Used to get product types)
                'bos:cart_calculate' => [
                    'href' => route('cart-calculate'),
                    'title' => 'Calculate cart',
                ],

                //  Link to search user using mobile number
                'bos:search_user_by_mobile_number' => [
                    'href' => route('search-user-by-mobile-number'),
                    'title' => 'POST to search user using their mobile number',
                ],

            ],

            /*  Embedded Resources */
            '_embedded' => [
                //  Me Resource (Set user profile)
                'me' => ($user = auth('api')->user()) ? (new UserResource($user)) : null,

                //  Set if authentication status
                'authenticated' => (auth('api')->user()) ? true : false,

                //  Main USSD shortcode
                'main_shortcode' => '*'.config('app.MAIN_SHORT_CODE').'#',

                //  Shortcode to verify user account after registration
                'verify_user_account_shortcode' => '*'.config('app.MAIN_SHORT_CODE').'*0000#'
            ],
        ];
    }

    /**
     * Customize the outgoing response for the resource.
     *
     * @param \Illuminate\Http\Request  $request
     * @param \Illuminate\Http\Response $response
     *
     * @return void
     */
    public function withResponse($request, $response)
    {
        $response->header('Content-Type', 'application/hal+json');
    }
}
