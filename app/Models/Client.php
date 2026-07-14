<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Client extends Model
{
    use HasFactory;

    protected $fillable = [
        'company_name',
        'gst_number',
        'corporate_address',
    ];

    /**
     * Get the plants associated with the client.
     */
    public function plants()
    {
        return $this->hasMany(ClientPlant::class, 'client_id');
    }

    /**
     * Get the delivery challans for the client.
     */
    public function deliveryChallans()
    {
        return $this->hasMany(DeliveryChallan::class, 'client_id');
    }
}
