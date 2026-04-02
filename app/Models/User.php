<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;


class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'photo',
        'is_active',
    ];

    public function isAdmin(): bool
    {
        return strcasecmp($this->role, 'admin') === 0;
    }

    public function isManager(): bool
    {
        return strcasecmp($this->role, 'manager') === 0;
    }

    public function isDriver(): bool
    {
        return strcasecmp($this->role, 'driver') === 0;
    }

    public function isClient(): bool
    {
        return strcasecmp($this->role, 'client') === 0;
    }


    public function client()
    {
        return $this->hasOne(Client::class);
    }

    public function driver()
    {
        return $this->hasOne(Driver::class);
    }

    public function manager()
    {
        return $this->hasOne(Manager::class);
    }

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }
   
}
