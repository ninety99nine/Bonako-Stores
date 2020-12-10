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
        return [
            'id' => $this->id,
            'first_name' => $this->first_name,
            'last_name' => $this->last_name,
            'email' => $this->email,
            'mobile_number' => $this->mobile_number,

            /*  Timestamp Info  */
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,

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

                //  Link to the user's stores
                'bos:stores' => [
                    'href' => route('my-stores'),
                    'title' => 'The stores that are created or shared with this user',
                    'total' => $this->stores()->count(),
                ],

                //  Link to the user's created stores
                'bos:created-stores' => [
                    'href' => route('my-created-stores'),
                    'title' => 'The stores that are created by this user',
                    'total' => $this->stores()->asOwner($this->id)->count(),
                ],
                
                //  Link to the user's shared stores
                'bos:shared-stores' => [
                    'href' => route('my-shared-stores'),
                    'title' => 'The stores that are shared with this user',
                    'total' => $this->stores()->asNonOwner($this->id)->count(),
                ],

                //  Link to the user's favourite stores
                'bos:favourite_stores' => [
                    'href' => route('my-favourite-stores'),
                    'title' => 'This user\'s favourite stores',
                    'total' => $this->stores()->asFavourite($this->id)->count(),
                ],
            ],

            /*  Embedded Resources */
            '_embedded' => [],
        ];
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
