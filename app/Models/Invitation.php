<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Invitation extends Model
{
    use HasFactory;

    protected $fillable = [
        'team_id',
        'sender_id',
        'recipient_email',
        'token'
    ];

    public function team()
    {
        return $this->belongsTo(Team::class);
    }

    public function sender()
    {
        return $this->hasOne(User::class, 'sender_id');
    }

    public function recipient()
    {
        return $this->hasOne(User::class, 'recipient_email', 'email');
    }
}
