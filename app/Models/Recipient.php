<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Recipient extends Model
{
    protected $fillable = ['last_name', 'first_name', 'email', 'phone'];

    public function orders()
    {
        return $this->hasMany(Order::class);
    }
}
