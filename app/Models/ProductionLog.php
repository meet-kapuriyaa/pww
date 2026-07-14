<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class ProductionLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'finished_good_id',
        'quantity_manufactured',
        'quantity_rejected',
        'recorded_by',
        'production_date',
    ];

    protected $casts = [
        'quantity_manufactured' => 'integer',
        'quantity_rejected' => 'integer',
        'production_date' => 'date',
    ];

    /**
     * Get the finished good that was manufactured.
     */
    public function finishedGood()
    {
        return $this->belongsTo(FinishedGood::class, 'finished_good_id');
    }

    /**
     * Get the user who recorded this log.
     */
    public function recordedByUser()
    {
        return $this->belongsTo(User::class, 'recorded_by');
    }

    /**
     * Get the labor logs associated with this production.
     */
    public function laborLogs()
    {
        return $this->hasMany(LaborLog::class, 'production_log_id');
    }
}
