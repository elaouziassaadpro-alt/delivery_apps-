<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Validation\ValidationException;

class Order extends Model
{
    use HasFactory;

    protected $fillable = [
        'bon_id', 
        'bon_driver_id', 
        'driver_id', 
        'recipient_id', 
        'vehicle_id', 
        'code', 
        'qr_file', 
        'location', 
        'price', 
        'driver_commission', 
        'commission', 
        'vehicle_license_plate', 
        'status', 
        'lat', 
        'lng'
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($order) {
            // Check if code exists; though a database UNIQUE constraint is safer
            if (static::where('code', $order->code)->exists()) {
                throw ValidationException::withMessages([
                    'code' => 'The order code must be unique.'
                ]);
            }
        });
    }

    /**
     * Relationships
     */

    public function bon(): BelongsTo
    {
        return $this->belongsTo(Bon::class);
    }

    public function driver(): BelongsTo
    {
        return $this->belongsTo(Driver::class);
    }

    public function recipient(): BelongsTo
    {
        return $this->belongsTo(Recipient::class);
    }

    public function vehicle(): BelongsTo
    {
        return $this->belongsTo(Vehicle::class);
    }

    public function bonDriver(): BelongsTo
    {
        // Explicitly defining the foreign key 'bon_driver_id'
        return $this->belongsTo(Bon::class, 'bon_driver_id');
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