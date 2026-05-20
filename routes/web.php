<?php

use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ExpenseController;
use App\Http\Controllers\SalesController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\CreditController;
use App\Http\Controllers\InvoiceController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\SettingsController;
use App\Http\Controllers\AnalyticsController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;

// Redirect root to dashboard (auth middleware handles login redirect)
Route::get('/', function () {
    return redirect()->route('dashboard');
});

Route::middleware(['auth', 'role'])->group(function () {
    // Dashboard Route (checks active and routes based on role)
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // Profile Routes (default Breeze)
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // Expense Management
    Route::resource('expenses', ExpenseController::class)->except(['show']);
    Route::post('/expenses/categories', [ExpenseController::class, 'storeCategory'])->name('expenses.categories.store');

    // Sales Module
    Route::resource('sales', SalesController::class);

    // Inventory Module (Products)
    Route::resource('inventory', ProductController::class)->parameters([
        'inventory' => 'product'
    ]);

    // Credit & Udhaar Module
    Route::resource('credits', CreditController::class);
    Route::post('/credits/{credit}/payments', [CreditController::class, 'storePayment'])->name('credits.payments.store');
    Route::post('/credits/{credit}/reminder', [CreditController::class, 'sendReminder'])->name('credits.reminder');

    // Invoices Module
    Route::get('/invoices/{sale}', [InvoiceController::class, 'show'])->name('invoices.show');
    Route::get('/invoices/{sale}/print', [InvoiceController::class, 'print'])->name('invoices.print');
    Route::get('/invoices/{sale}/pdf', [InvoiceController::class, 'downloadPDF'])->name('invoices.pdf');
    Route::post('/invoices/{sale}/share', [InvoiceController::class, 'share'])->name('invoices.share');

    // Reports Module (restricted to Owner and Accountant via Controller and middleware)
    Route::middleware('role:owner,accountant')->group(function () {
        Route::get('/reports', [ReportController::class, 'index'])->name('reports.index');
        Route::post('/reports/export', [ReportController::class, 'export'])->name('reports.export');
        
        // Analytics Module
        Route::get('/analytics', [AnalyticsController::class, 'index'])->name('analytics.index');
    });

    // Settings Module (restricted to Owner only)
    Route::middleware('role:owner')->group(function () {
        Route::get('/settings', [SettingsController::class, 'index'])->name('settings.index');
        Route::post('/settings/shop', [SettingsController::class, 'updateShop'])->name('settings.shop.update');
        Route::post('/settings/password', [SettingsController::class, 'changePassword'])->name('settings.password.update');
        Route::post('/settings/staff', [SettingsController::class, 'storeStaff'])->name('settings.staff.store');
        Route::post('/settings/staff/{user}/toggle', [SettingsController::class, 'toggleStaffStatus'])->name('settings.staff.toggle');
    });

    // Notifications Module
    Route::get('/notifications', [NotificationController::class, 'index'])->name('notifications.index');
    Route::post('/notifications/{id}/read', [NotificationController::class, 'markAsRead'])->name('notifications.read');
    Route::post('/notifications/read-all', [NotificationController::class, 'markAllAsRead'])->name('notifications.read-all');
    Route::delete('/notifications/{id}', [NotificationController::class, 'destroy'])->name('notifications.destroy');
});

require __DIR__.'/auth.php';
