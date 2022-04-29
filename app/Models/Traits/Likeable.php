<?php

namespace App\Models\Traits;

use App\Models\Like;

trait Likeable
{

    public static function bootLikeable()
    {
        static::deleting(function ($model) {
            $model->removeLikes();
        });
    }

    // delete like when the model being deleted
    public function removeLikes()
    {
        if ($this->likes()->count()) {
            $this->likes()->delete();
        }
    }

    public function likes()
    {
        return $this->morphMany(Like::class, 'likeable');
    }

    public function like()
    {
        if (!auth()->check()) {
            return;
        }
        // chek if the current user has already like
        if ($this->isLikeByUser(auth()->id())) {
            return;
        }

        $this->likes()->create(['user_id' => auth()->id()]);
    }

    public function unlike()
    {
        if (!auth()->check()) return;

        // chek if the current user has no like
        if (!$this->isLikeByUser(auth()->id())) {
            return;
        }

        $this->likes()->where('user_id', auth()->id())->delete();
    }

    public function isLikeByUser($user_id)
    {
        return (bool)$this->likes()->where('user_id', $user_id)->count();
    }
}
