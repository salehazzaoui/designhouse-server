<?php

namespace App\Models;

use App\Models\Traits\Likeable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class Design extends Model
{
    use HasFactory, likeable;

    protected $fillable = [
        'user_id',
        'team_id',
        'image',
        'title',
        'description',
        'slug',
        'is_live',
        'upload_successful'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function comments()
    {
        return $this->morphMany(Comment::class, 'commentable')->orderby('created_at', 'desc');
    }

    public function team()
    {
        return $this->belongsTo(Team::class);
    }

    public function getImagesAttribute()
    {
        return [
            'thumbnail' => $this->getImagePath('thumbnail'),
            'original' => $this->getImagePath('original'),
            'large' => $this->getImagePath('large')
        ];
    }

    protected function getImagePath($size)
    {
        return config('app.url') . '' . Storage::url("designs/{$size}/" . $this->image);
    }
}
