<?php

namespace App\Models;


use Illuminate\Database\Eloquent\Model;
use App\Models\Manager;
use App\Models\Order;

class Vehicle extends Model
{
    protected $fillable = ['registration_card', 'license_plate', 'make', 'manager_id', 'type'];

    public function manager()
    {
        return $this->belongsTo(Manager::class);
    }

    public function orders()
    {
        return $this->hasMany(Order::class);
    }
}
