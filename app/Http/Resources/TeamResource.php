<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class TeamResource extends JsonResource
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
            'slug' => $this->slug,
            'owner' => new UserResource($this->owner),
            'membres' => UserResource::collection($this->membres),
            'designs' => UserResource::collection($this->designs),
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
