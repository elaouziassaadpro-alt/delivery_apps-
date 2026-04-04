<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Validation\ValidationException;
use App\Models\Client;
use App\Models\Driver;
use App\Models\Recipient;
use App\Models\Vehicle;

class Order extends Model
{
    protected $fillable = [
        'bon_id', 'bon_driver_id', 'driver_id', 'recipient_id', 'vehicle_id', 
        'code', 'qr_file', 'location', 'price', 
        'driver_commission', 'commission', 'vehicle_license_plate', 'status', 'lat', 'lng'
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($order) {
            if (self::where('code', $order->code)->exists()) {
                throw ValidationException::withMessages([
                    'code' => 'The code already exists.'
                ]);
            }
        });
    }

    /**
     * Get the bon that owns the order.
     */
    public function bon()
    {
        return $this->belongsTo(Bon::class);
    }


    public function driver()
    {
        return $this->belongsTo(Driver::class);
    }

    public function recipient()
    {
        return $this->belongsTo(Recipient::class);
    }

    public function vehicle()
    {
        return $this->belongsTo(Vehicle::class);
    }
}
