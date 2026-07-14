<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Invoice extends Model
{
    use HasFactory;

    protected $fillable = [
        'delivery_challan_id',
        'invoice_number',
        'total_taxable_value',
        'cgst',
        'sgst',
        'igst',
        'total_amount',
        'payment_status',
        'paid_amount',
        'due_date',
    ];

    protected $casts = [
        'total_taxable_value' => 'decimal:2',
        'cgst' => 'decimal:2',
        'sgst' => 'decimal:2',
        'igst' => 'decimal:2',
        'total_amount' => 'decimal:2',
        'paid_amount' => 'decimal:2',
        'due_date' => 'date',
    ];

    /**
     * Get the primary delivery challan if single-challan invoice.
     */
    public function deliveryChallan()
    {
        return $this->belongsTo(DeliveryChallan::class, 'delivery_challan_id');
    }

    /**
     * Get all delivery challans aggregated in this invoice.
     */
    public function deliveryChallans()
    {
        return $this->hasMany(DeliveryChallan::class, 'invoice_id');
    }
}
