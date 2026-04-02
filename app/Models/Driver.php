<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Vehicle;

class Driver extends Model
{
    protected $fillable = [
        'user_id', 'vehicle_id', 'last_name', 'first_name', 'id_card_number', 
        'id_card_file', 'contract', 'phone', 'email', 
        'commission', 'photo'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function vehicle()
    {
        return $this->belongsTo(Vehicle::class);
    }

    public function orders()
    {
        return $this->hasMany(Order::class);
    }
}
