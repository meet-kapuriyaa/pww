<?php

namespace App\Services;

use App\Models\DeliveryChallan;
use App\Models\Invoice;
use App\Models\ClientPlant;
use Illuminate\Support\Facades\DB;
use Exception;

class BillingService
{
    /**
     * Convert one or multiple delivery challans into a compliance tax invoice.
     *
     * @param array $challanIds
     * @param string|null $dueDate
     * @return Invoice
     * @throws Exception
     */
    public function createInvoiceFromChallans(array $challanIds, ?string $dueDate = null): Invoice
    {
        if (empty($challanIds)) {
            throw new Exception("No delivery challans selected.");
        }

        return DB::transaction(function () use ($challanIds, $dueDate) {
            // Fetch challans with items and plant details
            $challans = DeliveryChallan::whereIn('id', $challanIds)
                ->where('status', 'pending_invoice')
                ->with(['items.finishedGood', 'plant'])
                ->get();

            if ($challans->count() !== count($challanIds)) {
                throw new Exception("One or more selected challans are invalid or already invoiced.");
            }

            // Ensure they all belong to the same client
            $clientId = $challans->first()->client_id;
            foreach ($challans as $challan) {
                if ($challan->client_id !== $clientId) {
                    throw new Exception("All selected challans must belong to the same client.");
                }
            }

            // Determine destination state from the first challan's plant
            $plant = $challans->first()->plant;
            $destinationState = trim($plant->state ?? 'Gujarat');
            $isGujarat = strcasecmp($destinationState, 'Gujarat') === 0;

            // Calculate total taxable value
            $totalTaxableValue = 0.00;
            foreach ($challans as $challan) {
                foreach ($challan->items as $item) {
                    $totalTaxableValue += $item->quantity * $item->unit_price;
                }
            }

            // Calculate GST
            $cgst = 0.00;
            $sgst = 0.00;
            $igst = 0.00;

            if ($isGujarat) {
                // CGST + SGST (9% + 9% = 18%)
                $cgst = round($totalTaxableValue * 0.09, 2);
                $sgst = round($totalTaxableValue * 0.09, 2);
            } else {
                // IGST (18%)
                $igst = round($totalTaxableValue * 0.18, 2);
            }

            $totalAmount = $totalTaxableValue + $cgst + $sgst + $igst;

            // Generate unique invoice number: INV-{timestamp}-{random_4_digits}
            $invoiceNumber = 'INV-' . date('Ymd') . '-' . str_pad(rand(1, 9999), 4, '0', STR_PAD_LEFT);
            
            // Just double check uniqueness
            while (Invoice::where('invoice_number', $invoiceNumber)->exists()) {
                $invoiceNumber = 'INV-' . date('Ymd') . '-' . str_pad(rand(1, 9999), 4, '0', STR_PAD_LEFT);
            }

            // Create Invoice
            $invoice = Invoice::create([
                'delivery_challan_id' => $challans->first()->id, // primary linked challan
                'invoice_number' => $invoiceNumber,
                'total_taxable_value' => $totalTaxableValue,
                'cgst' => $cgst,
                'sgst' => $sgst,
                'igst' => $igst,
                'total_amount' => $totalAmount,
                'payment_status' => 'unpaid',
                'paid_amount' => 0.00,
                'due_date' => $dueDate ?? date('Y-m-d', strtotime('+30 days')),
            ]);

            // Associate challans with this invoice and update status
            foreach ($challans as $challan) {
                $challan->update([
                    'status' => 'invoiced',
                    'invoice_id' => $invoice->id,
                ]);
            }

            return $invoice;
        });
    }
}
