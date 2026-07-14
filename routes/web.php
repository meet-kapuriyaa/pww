<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\ErpController;

// Authentication routes
Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
Route::post('/login', [AuthController::class, 'login']);
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

// Gated ERP routes
Route::middleware(['auth'])->group(function () {
    Route::get('/', function () {
        return redirect()->route('overview');
    });

    // 1. Dashboard Overview
    Route::get('/overview', [ErpController::class, 'overview'])->name('overview');

    // 2. Inventory Management
    Route::get('/inventory', [ErpController::class, 'inventory'])->name('inventory');
    Route::post('/inventory/materials', [ErpController::class, 'storeRawMaterial'])->name('inventory.materials.store');
    Route::post('/inventory/goods', [ErpController::class, 'storeFinishedGood'])->name('inventory.goods.store');

    // 3. Bill of Materials
    Route::get('/bom', [ErpController::class, 'bom'])->name('bom');
    Route::post('/bom', [ErpController::class, 'storeBom'])->name('bom.store');

    // 4. Production Logs
    Route::get('/production', [ErpController::class, 'production'])->name('production');
    Route::post('/production', [ErpController::class, 'logProduction'])->name('production.store');

    // 5. Clients & Plants
    Route::get('/clients', [ErpController::class, 'clients'])->name('clients');
    Route::post('/clients', [ErpController::class, 'storeClient'])->name('clients.store');
    Route::post('/clients/plants', [ErpController::class, 'storePlant'])->name('clients.plants.store');

    // 6. Delivery Challans
    Route::get('/challans', [ErpController::class, 'challans'])->name('challans');
    Route::post('/challans', [ErpController::class, 'storeChallan'])->name('challans.store');

    // 7. Invoices & Billing Page
    Route::get('/invoices', [ErpController::class, 'invoices'])->name('invoices');
    Route::post('/invoices', [ErpController::class, 'createInvoice'])->name('invoice.create');
    Route::post('/invoices/generate', [ErpController::class, 'generateCustomInvoice'])->name('invoice.generate');

    // 8. Employees Directory
    Route::get('/employees', [ErpController::class, 'employees'])->name('employees');
    Route::post('/employees', [ErpController::class, 'storeEmployee'])->name('employees.store');

    // 9. Operational Expenses
    Route::get('/expenses', [ErpController::class, 'expenses'])->name('expenses');
    Route::post('/expenses', [ErpController::class, 'logExpense'])->name('expense.store');

    // 10. Reports & Export
    Route::get('/reports', [ErpController::class, 'reports'])->name('reports');
    Route::get('/reports/export', [ErpController::class, 'exportCsv'])->name('reports.export');

    // Reset demonstration utility
    Route::post('/reset-data', [ErpController::class, 'resetData'])->name('reset-data');

    // 11. Profile Management
    Route::get('/profile', [ErpController::class, 'profile'])->name('profile');
    Route::post('/profile/update', [ErpController::class, 'updateProfile'])->name('profile.update');
    Route::post('/profile/password', [ErpController::class, 'updatePassword'])->name('profile.password');
});

