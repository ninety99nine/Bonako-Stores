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

                //  Link to check if account exists
                'bos:account_exists' => [
                    'href' => route('account-exists'),
                    'title' => 'Check if user account exists',
                ],

                //  Link to send mobile account verification code
                'bos:send_mobile_account_verification_code' => [
                    'href' => route('send-mobile-account-verification-code'),
                    'title' => 'Send mobile account verification code via SMS to verify mobile number ownership',
                ],

                //  Link to check if account exists
                'bos:verify_mobile_account_verification_code' => [
                    'href' => route('verify-mobile-account-verification-code'),
                    'title' => 'Verify mobile account verification code to confirm mobile number ownership',
                ],

                //  Link to send password reset link
                'bos:send-password-reset-link' => [
                    'href' => route('send-password-reset-link'),
                    'title' => 'Send the password reset link',
                ],

                //  Link to reset password
                'bos:reset-password' => [
                    'href' => route('reset-password'),
                    'title' => 'Reset the user\'s password',
                ],

                //  Link to logout from current device
                'bos:logout' => [
                    'href' => route('logout'),
                    'title' => 'Logout from current device',
                ],

                //  Link to logout from all devices
                'bos:logout-everyone' => [
                    'href' => route('logout', ['everyone' => 'true']),
                    'title' => 'Logout all devices',
                ],

                //  Link to stores resources (Used to create new store resource)
                'bos:stores' => [
                    'href' => route('store-create'),
                    'title' => 'Get or create stores',
                ],

                //  Link to locations resources (Used to create new location resource)
                'bos:locations' => [
                    'href' => route('store-create'),
                    'title' => 'Create locations',
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


            ],

            /*  Embedded Resources */
            '_embedded' => [
                //  Me Resource (Set user profile)
                'me' => ($user = auth('api')->user()) ? (new UserResource($user)) : null,

                //  Set if authentication status
                'authenticated' => (auth('api')->user()) ? true : false,
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
