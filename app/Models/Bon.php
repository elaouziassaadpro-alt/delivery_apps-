<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Bon extends Model
{
    protected $fillable = [
        'code',
        'user_id',
        'status',
        'payment_status',
        'payment_method',
        'delivery_type',
        'pickup_date',
        'price',
        'driver_commission',
        'commission',
        'weight',
        'dimensions_length',
        'dimensions_width',
        'dimensions_height',
        'notes',
        'is_completed',
    ];

    /**
     * Get the user associated with the Bon.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the orders associated with the Bon.
     */
    public function orders(): HasMany
    {
        return $this->hasMany(Order::class );
    }
    public function orders_driver(): HasMany
    {
        return $this->hasMany(Order::class ,'bon_driver_id');
    }
    public function status_completed()
    {
        if($this->orders()->where('status', '!=', 'delivered')->count() == 0){
            $this->is_completed = 1;
            $this->save();
            return true;
        }
        $this->is_completed = 0;
        $this->save();
        return false;
    }
    public function color()
    {
        return match($this->status) {
            'completed'  => 'bg-green-100 text-green-800 outline-green-200',
            'pending'    => 'bg-yellow-100 text-yellow-800 outline-yellow-200',
            'In Transit' => 'bg-blue-100 text-blue-800 outline-blue-200',
            'cancelled'  => 'bg-red-100 text-red-800 outline-red-200',
            default      => 'bg-gray-100 text-gray-800 outline-gray-200',
        };
    }

    
    
}