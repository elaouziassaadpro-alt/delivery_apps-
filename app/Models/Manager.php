<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Manager extends Model
{
    protected $fillable = ['user_id', 'last_name', 'first_name', 'id_card_number', 'email', 'phone'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
    public function getFullNameAttribute(): string
    {
        return "{$this->first_name} {$this->last_name}";
    }
    public function vehicles()
    {
        return $this->hasMany(Vehicle::class);
    }
}
