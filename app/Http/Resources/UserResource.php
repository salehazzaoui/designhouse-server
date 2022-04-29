<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class UserResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'username' => $this->username,
            'avatar' => $this->getAvatarUrl(),
            'cover_image' => $this->getCoverImageUrl(),
            'tagline' => $this->tagline,
            'about' => $this->about,
            'formatted_adress' => $this->formatted_adress,
            'location' => [
                'latitude' => $this->latitude,
                'longitude' => $this->longitude
            ],
            'available_to_hire' => $this->available_to_hire,
            $this->mergeWhen(auth()->check() && auth()->id() == $this->id, [
                'email' => $this->email,
            ]),
            //'designs' => DesignResource::collection($this->designs),
            'created_date' => [
                'created_at' => $this->created_at,
                'created_at_human' => $this->created_at->diffForHumans()
            ],
            'updated_date' => [
                'updated_at' => $this->updated_at,
                'updated_at_human' => $this->updated_at->diffForHumans()
            ]
        ];
    }
}
