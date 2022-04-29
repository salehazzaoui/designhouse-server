<?php

namespace App\Models;

use App\Models\Traits\Likeable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Like extends Model
{
    use HasFactory, Likeable;

    protected $fillable = [
        'user_id'
    ];

    public function likeable()
    {
        return $this->morphTo();
    }
}
