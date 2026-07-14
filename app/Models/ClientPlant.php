<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class ClientPlant extends Model
{
    use HasFactory;

    protected $fillable = [
        'client_id',
        'plant_name',
        'shipping_address',
        'state',
    ];

    /**
     * Get the client parent profile.
     */
    public function client()
    {
        return $this->belongsTo(Client::class, 'client_id');
    }

    /**
     * Get the delivery challans sent to this plant.
     */
    public function deliveryChallans()
    {
        return $this->hasMany(DeliveryChallan::class, 'plant_id');
    }
}
