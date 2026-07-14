<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class StaffProfile extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'full_name',
        'wage_type',
        'monthly_salary',
        'piece_rate_per_unit',
    ];

    protected $casts = [
        'monthly_salary' => 'decimal:2',
        'piece_rate_per_unit' => 'decimal:2',
    ];

    /**
     * Get the user account associated with the staff member.
     */
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * Get the labor logs for the staff member.
     */
    public function laborLogs()
    {
        return $this->hasMany(LaborLog::class, 'staff_profile_id');
    }
}
