<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class user extends JsonResource
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
        $data = [
            'id' => $this->id,
            'first_name' => $this->first_name,
            'last_name' => $this->last_name,
            'email' => $this->email,
            'mobile_number' => $this->mobile_number,

            /*  Timestamp Info  */
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,

            '_attributes' => [
                'name' => $this->name
            ],

            /*  Resource Links */
            '_links' => [
                'curies' => [
                    ['name' => 'oq', 'href' => 'https://oqcloud.co.bw/docs/rels/{rel}', 'templated' => true],
                ],

                //  Link to current resource
                'self' => [
                    'href' => route('my-profile'),
                    'title' => 'This user',
                ],

                //  Link to user addresses
                'bos:addresses' => [
                    'href' => route('my-addresses'),
                    'title' => 'The user addresses'
                ],

                //  Link to user subscriptions
                'bos:subscriptions' => [
                    'href' => route('my-subscriptions'),
                    'title' => 'The user subscriptions'
                ]
            ],

            /*  Embedded Resources */
            '_embedded' => [],
        ];

        /**
         *  Additional Links
         *
         *  Note that "auth('api')->user()" is only accessible when we have an API Bearer Token provided
         *  e.g when we access the Api Home route ".../api". This is because Laravel will use the Bearer
         *  Token to get the authenticated user. However if the Bearer Token is not provided e.g when we
         *  are logging in then we cannot use the method to acquire a logged in user.
         *
         *  However since we force the user to login using the "auth()->loginUsingId($user->id)" method
         *  found within the AuthController, we are able to access the instance of that authenticated
         *  user directly using the "auth()->user()->id" method.
         *
         *  We use both these techniques to fetch the authenticated user so that we can check if this
         *  User Resource id matches the authenticated user id. If it does then it means that the user
         *  is the owner and therefore we can load additional routes.
         */
        if( ( (auth('api')->user()->id ?? auth()->user()->id) ) === $this->id ){

            $data['_links'] = array_merge($data['_links'], [

                //  Link to the user's stores
                'bos:stores' => [
                    'href' => route('my-stores'),
                    'title' => 'The stores that are created, shared or favourited by this user',
                ],

                //  Link to the user's favourite stores
                'bos:favourite-stores' => [
                    'href' => route('my-favourite-stores'),
                    'title' => 'The stores favourited by this user',
                ],

                //  Link to the user's shared stores
                'bos:shared-stores' => [
                    'href' => route('my-shared-stores'),
                    'title' => 'The stores shared by this user',
                ],

                //  Link to the user's created stores
                'bos:created-stores' => [
                    'href' => route('my-created-stores'),
                    'title' => 'The stores created by this user',
                ],

            ]);

        }

        return $data;

    }

    /**
     * Customize the outgoing response for the resource.
     *
     * @param \Illuminate\Http\Request  $request
     * @param \Illuminate\Http\Response $response
     */
    public function withResponse($request, $response)
    {
        $response->header('Content-Type', 'application/hal+json');
    }
}
