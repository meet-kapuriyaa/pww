<?php

namespace App\Services;

use App\Models\LaborLog;
use App\Models\StaffProfile;
use Illuminate\Support\Facades\DB;

class PayrollService
{
    /**
     * Get compiled pending payouts for all piece-rate staff members.
     *
     * @return \Illuminate\Support\Collection
     */
    public function compilePendingPieceRateWages()
    {
        return StaffProfile::where('wage_type', 'piece-rate')
            ->with(['laborLogs' => function ($query) {
                $query->where('status', 'pending');
            }])
            ->get()
            ->map(function ($staff) {
                $pendingLogs = $staff->laborLogs;
                $totalUnits = $pendingLogs->sum('units_completed');
                $totalPayout = $pendingLogs->sum('calculated_payout');
                
                return [
                    'staff_profile_id' => $staff->id,
                    'full_name' => $staff->full_name,
                    'piece_rate_per_unit' => $staff->piece_rate_per_unit,
                    'pending_logs_count' => $pendingLogs->count(),
                    'total_units_completed' => $totalUnits,
                    'total_pending_payout' => $totalPayout,
                    'log_ids' => $pendingLogs->pluck('id')->toArray(),
                ];
            })
            ->filter(function ($staff) {
                return $staff['pending_logs_count'] > 0;
            })
            ->values();
    }

    /**
     * Mark a set of labor logs as paid.
     *
     * @param array $laborLogIds
     * @return int Number of updated rows
     */
    public function markWagesAsPaid(array $laborLogIds): int
    {
        if (empty($laborLogIds)) {
            return 0;
        }

        return LaborLog::whereIn('id', $laborLogIds)
            ->where('status', 'pending')
            ->update(['status' => 'paid']);
    }
}
