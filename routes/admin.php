<?php

use App\Http\Controllers\Admin\AdminDashboardController;
use App\Http\Controllers\Admin\PhotographerController;
use App\Http\Controllers\Admin\AlbumController;
use App\Http\Controllers\Admin\RevenueController;
use App\Http\Controllers\Admin\AuditLogController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'admin'])->prefix('admin')->name('admin.')->group(function () {
    // Dashboard
    Route::get('/', [AdminDashboardController::class, 'index'])->name('dashboard');
    Route::get('/dashboard', [AdminDashboardController::class, 'index'])->name('dashboard.index');

    // Photographer Management
    Route::resource('photographers', PhotographerController::class);
    Route::post('photographers/{photographer}/toggle-status', [PhotographerController::class, 'toggleStatus'])
        ->name('photographers.toggle-status');

    // Album Management
    Route::resource('albums', AlbumController::class);

    // Revenue Analytics
    Route::get('revenue', [RevenueController::class, 'index'])->name('revenue.index');

    // Audit Logs
    Route::get('audit-logs', [AuditLogController::class, 'index'])->name('audit-logs.index');
    Route::get('audit-logs/{auditLog}', [AuditLogController::class, 'show'])->name('audit-logs.show');
});
