<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class DeliveryChallanItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'delivery_challan_id',
        'finished_good_id',
        'quantity',
        'unit_price',
    ];

    protected $casts = [
        'quantity' => 'integer',
        'unit_price' => 'decimal:2',
    ];

    /**
     * Get the delivery challan parent.
     */
    public function deliveryChallan()
    {
        return $this->belongsTo(DeliveryChallan::class, 'delivery_challan_id');
    }

    /**
     * Get the finished good product.
     */
    public function finishedGood()
    {
        return $this->belongsTo(FinishedGood::class, 'finished_good_id');
    }
}
