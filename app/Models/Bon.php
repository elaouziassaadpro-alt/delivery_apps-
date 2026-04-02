<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Bon extends Model
{
    protected $fillable = [
        'code',
        'client_id',
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
    ];

    /**
     * Get the client associated with the Bon.
     */
    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }

    /**
     * Get the orders associated with the Bon.
     */
    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
    }
}