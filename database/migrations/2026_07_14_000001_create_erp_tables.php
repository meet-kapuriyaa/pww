<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // 1. raw_materials
        Schema::create('raw_materials', function (Blueprint $table) {
            $table->id();
            $table->string('material_name');
            $table->string('unit'); // e.g. kg, liters
            $table->decimal('current_stock', 12, 4)->default(0.0000);
            $table->decimal('safety_threshold', 12, 4)->default(0.0000);
            $table->decimal('average_purchase_price', 12, 2)->default(0.00);
            $table->timestamps();
        });

        // 2. finished_goods
        Schema::create('finished_goods', function (Blueprint $table) {
            $table->id();
            $table->string('product_name');
            $table->string('sku')->unique();
            $table->integer('current_stock')->default(0);
            $table->decimal('selling_price', 12, 2)->default(0.00);
            $table->boolean('alerts_enabled')->default(true);
            $table->timestamps();
        });

        // 3. bill_of_materials (BOM)
        Schema::create('bill_of_materials', function (Blueprint $table) {
            $table->id();
            $table->foreignId('finished_good_id')->constrained('finished_goods')->onDelete('cascade');
            $table->foreignId('raw_material_id')->constrained('raw_materials')->onDelete('cascade');
            $table->decimal('required_quantity', 12, 4);
            $table->decimal('waste_percentage', 5, 2)->default(0.00);
            $table->timestamps();

            $table->index(['finished_good_id', 'raw_material_id']);
        });

        // 4. production_logs
        Schema::create('production_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('finished_good_id')->constrained('finished_goods')->onDelete('cascade');
            $table->integer('quantity_manufactured');
            $table->integer('quantity_rejected')->default(0);
            $table->foreignId('recorded_by')->constrained('users')->onDelete('cascade');
            $table->date('production_date');
            $table->timestamps();

            $table->index('finished_good_id');
            $table->index('recorded_by');
            $table->index('production_date');
        });

        // 5. clients
        Schema::create('clients', function (Blueprint $table) {
            $table->id();
            $table->string('company_name')->default('Balaji Wafers');
            $table->string('gst_number')->nullable();
            $table->text('corporate_address')->nullable();
            $table->timestamps();
        });

        // 6. client_plants
        Schema::create('client_plants', function (Blueprint $table) {
            $table->id();
            $table->foreignId('client_id')->constrained('clients')->onDelete('cascade');
            $table->string('plant_name'); // e.g. Rajkot, Valsad, Indore
            $table->text('shipping_address')->nullable();
            $table->string('state')->default('Gujarat');
            $table->timestamps();

            $table->index('client_id');
        });

        // 7. delivery_challans
        Schema::create('delivery_challans', function (Blueprint $table) {
            $table->id();
            $table->foreignId('client_id')->constrained('clients')->onDelete('cascade');
            $table->foreignId('plant_id')->constrained('client_plants')->onDelete('cascade');
            $table->string('challan_number')->unique();
            $table->date('dispatch_date');
            $table->enum('status', ['pending_invoice', 'invoiced'])->default('pending_invoice');
            $table->timestamps();

            $table->index('client_id');
            $table->index('plant_id');
            $table->index('challan_number');
        });

        // 8. delivery_challan_items (to store contents of challan)
        Schema::create('delivery_challan_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('delivery_challan_id')->constrained('delivery_challans')->onDelete('cascade');
            $table->foreignId('finished_good_id')->constrained('finished_goods')->onDelete('cascade');
            $table->integer('quantity');
            $table->decimal('unit_price', 12, 2)->default(0.00);
            $table->timestamps();

            $table->index('delivery_challan_id');
            $table->index('finished_good_id');
        });

        // 9. invoices
        Schema::create('invoices', function (Blueprint $table) {
            $table->id();
            $table->foreignId('delivery_challan_id')->nullable()->constrained('delivery_challans')->onDelete('set null');
            $table->string('invoice_number')->unique();
            $table->decimal('total_taxable_value', 12, 2)->default(0.00);
            $table->decimal('cgst', 12, 2)->default(0.00);
            $table->decimal('sgst', 12, 2)->default(0.00);
            $table->decimal('igst', 12, 2)->default(0.00);
            $table->decimal('total_amount', 12, 2)->default(0.00);
            $table->enum('payment_status', ['unpaid', 'partially_paid', 'paid'])->default('unpaid');
            $table->decimal('paid_amount', 12, 2)->default(0.00);
            $table->date('due_date');
            $table->timestamps();

            $table->index('delivery_challan_id');
            $table->index('invoice_number');
        });

        // Add optional invoice_id to delivery_challans for many-to-one invoice aggregation
        Schema::table('delivery_challans', function (Blueprint $table) {
            $table->foreignId('invoice_id')->nullable()->constrained('invoices')->onDelete('set null');
            $table->index('invoice_id');
        });

        // 10. staff_profiles
        Schema::create('staff_profiles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained('users')->onDelete('set null');
            $table->string('full_name');
            $table->enum('wage_type', ['fixed', 'piece-rate']);
            $table->decimal('monthly_salary', 12, 2)->nullable();
            $table->decimal('piece_rate_per_unit', 12, 2)->nullable();
            $table->timestamps();

            $table->index('user_id');
        });

        // 11. labor_logs
        Schema::create('labor_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('staff_profile_id')->constrained('staff_profiles')->onDelete('cascade');
            $table->foreignId('production_log_id')->constrained('production_logs')->onDelete('cascade');
            $table->integer('units_completed');
            $table->decimal('calculated_payout', 12, 2)->default(0.00);
            $table->enum('status', ['pending', 'paid'])->default('pending');
            $table->timestamps();

            $table->index('staff_profile_id');
            $table->index('production_log_id');
        });

        // 12. expenses
        Schema::create('expenses', function (Blueprint $table) {
            $table->id();
            $table->enum('expense_category', [
                'factory_electricity',
                'industrial_gas',
                'welding_consumables',
                'freight_transport',
                'office_rent',
                'administrative',
                'machinery_depreciation'
            ]);
            $table->decimal('amount', 12, 2);
            $table->date('expense_date');
            $table->text('description')->nullable();
            $table->timestamps();

            $table->index('expense_category');
            $table->index('expense_date');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('delivery_challans', function (Blueprint $table) {
            $table->dropForeign(['invoice_id']);
            $table->dropColumn('invoice_id');
        });

        Schema::dropIfExists('expenses');
        Schema::dropIfExists('labor_logs');
        Schema::dropIfExists('staff_profiles');
        Schema::dropIfExists('invoices');
        Schema::dropIfExists('delivery_challan_items');
        Schema::dropIfExists('delivery_challans');
        Schema::dropIfExists('client_plants');
        Schema::dropIfExists('clients');
        Schema::dropIfExists('production_logs');
        Schema::dropIfExists('bill_of_materials');
        Schema::dropIfExists('finished_goods');
        Schema::dropIfExists('raw_materials');
    }
};
