<?php

namespace App\Services;

use App\Models\FinishedGood;
use App\Models\RawMaterial;
use App\Models\ProductionLog;
use App\Models\LaborLog;
use App\Models\StaffProfile;
use App\Exceptions\InsufficientStockException;
use Illuminate\Support\Facades\DB;

class ProductionService
{
    /**
     * Record a production batch and auto-deduct raw material inventory.
     *
     * @param int $finishedGoodId
     * @param int $quantityManufactured
     * @param int $quantityRejected
     * @param int $recordedByUserId
     * @param string $productionDate
     * @param array $laborData array of ['staff_profile_id' => x, 'units_completed' => y]
     * @return ProductionLog
     * @throws InsufficientStockException
     */
    public function logProduction(
        int $finishedGoodId,
        int $quantityManufactured,
        int $quantityRejected,
        int $recordedByUserId,
        string $productionDate,
        array $laborData = []
    ): ProductionLog {
        return DB::transaction(function () use ($finishedGoodId, $quantityManufactured, $quantityRejected, $recordedByUserId, $productionDate, $laborData) {
            $finishedGood = FinishedGood::findOrFail($finishedGoodId);

            // Fetch BOM items
            $bomItems = $finishedGood->billOfMaterials()->with('rawMaterial')->get();

            // Check and deduct raw materials
            foreach ($bomItems as $bom) {
                $rawMaterial = $bom->rawMaterial;
                
                // Total Consumed = quantity_manufactured * required_quantity * (1 + (waste_percentage / 100))
                $wasteMultiplier = 1 + ($bom->waste_percentage / 100);
                $totalConsumed = $quantityManufactured * $bom->required_quantity * $wasteMultiplier;

                if ($rawMaterial->current_stock < $totalConsumed) {
                    throw new InsufficientStockException(
                        $rawMaterial->material_name,
                        $totalConsumed,
                        $rawMaterial->current_stock
                    );
                }

                // Deduct stock
                $rawMaterial->decrement('current_stock', $totalConsumed);
            }

            // Increment finished goods stock
            $finishedGood->increment('current_stock', $quantityManufactured);

            // Create production log
            $productionLog = ProductionLog::create([
                'finished_good_id' => $finishedGoodId,
                'quantity_manufactured' => $quantityManufactured,
                'quantity_rejected' => $quantityRejected,
                'recorded_by' => $recordedByUserId,
                'production_date' => $productionDate,
            ]);

            // Create labor logs if provided
            foreach ($laborData as $labor) {
                if (empty($labor['staff_profile_id']) || !isset($labor['units_completed'])) {
                    continue;
                }

                $staffProfile = StaffProfile::findOrFail($labor['staff_profile_id']);
                
                // Calculate payout if wage_type is piece-rate
                $payout = 0.00;
                if ($staffProfile->wage_type === 'piece-rate') {
                    $payout = $labor['units_completed'] * ($staffProfile->piece_rate_per_unit ?? 0.00);
                }

                LaborLog::create([
                    'staff_profile_id' => $staffProfile->id,
                    'production_log_id' => $productionLog->id,
                    'units_completed' => $labor['units_completed'],
                    'calculated_payout' => $payout,
                    'status' => 'pending',
                ]);
            }

            return $productionLog;
        });
    }
}
