<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Auth;

class DesignResource extends JsonResource
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
            'title' => $this->title,
            'user' => new UserResource($this->user),
            'slug' => $this->slug,
            'description' => $this->description,
            'images' => $this->images,
            'is_live' => $this->is_live,
            'likes' => $this->likes->count(),
            'is_likes' => $this->isLikeByUser(auth()->id()),
            'team' => $this->team ? [
                'name' => $this->team->name,
                'slug' => $this->team->slug
            ] : null,
            'comments' => CommentResource::collection($this->comments),
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
