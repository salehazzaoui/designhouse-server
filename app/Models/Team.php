<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Team extends Model
{
    use HasFactory;

    protected $fillable = [
        'owner_id',
        'name',
        'slug'
    ];

    public static function boot()
    {
        parent::boot();

        // when team is created, add current user as
        // team member
        static::created(function ($team) {
            $team->membres()->attach(auth()->id());
        });

        static::deleting(function ($team) {
            $team->membres()->sync([]);
        });
    }

    public function membres()
    {
        return $this->belongsToMany(User::class, 'team_user')->withTimestamps();
    }

    public function owner()
    {
        return $this->belongsTo(User::class, 'owner_id');
    }

    public function designs()
    {
        return $this->hasMany(Design::class);
    }

    public function invitations()
    {
        return $this->hasMany(Invitation::class);
    }

    public function hasPendingInvitation($email)
    {
        return (bool) $this->invitations()->where('recipient_email', $email)->count();
    }

    public function hasUser(User $recipient)
    {
        return $this->membres()->where('user_id', $recipient->id)->first() ? true : false;
    }
}
