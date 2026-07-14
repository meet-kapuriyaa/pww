<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class LaborLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'staff_profile_id',
        'production_log_id',
        'units_completed',
        'calculated_payout',
        'status',
    ];

    protected $casts = [
        'units_completed' => 'integer',
        'calculated_payout' => 'decimal:2',
    ];

    /**
     * Get the staff member.
     */
    public function staffProfile()
    {
        return $this->belongsTo(StaffProfile::class, 'staff_profile_id');
    }

    /**
     * Get the production log that this labor was part of.
     */
    public function productionLog()
    {
        return $this->belongsTo(ProductionLog::class, 'production_log_id');
    }
}
