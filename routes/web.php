<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\LandingController;
use App\Http\Controllers\AlbumController;
use App\Http\Controllers\PhotoController;
use App\Http\Controllers\CatalogController;
use App\Http\Controllers\CartController;
use App\Http\Controllers\CheckoutController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\PurchaseController;
use App\Http\Controllers\FaceScanController;
use App\Http\Controllers\BuyerFaceRegistrationController;
use App\Http\Controllers\BuyerDashboardController;
use Illuminate\Support\Facades\Route;

Route::get('/', [LandingController::class, 'index'])->name('landing');

// Public routes untuk albums (pembeli pilih album dulu)
Route::get('/albums', [App\Http\Controllers\AlbumCatalogController::class, 'index'])->name('albums.index');
Route::get('/albums/{album}', [App\Http\Controllers\AlbumCatalogController::class, 'show'])->name('albums.show');

// Old catalog route (deprecated, redirect ke albums)
Route::get('/catalog', function() {
    return redirect()->route('albums.index');
})->name('catalog.index');
Route::get('/catalog/{photo}', [CatalogController::class, 'show'])->name('catalog.show');

Route::get('/dashboard', [DashboardController::class, 'index'])->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    // Route untuk Shopping Cart (Buyer)
    Route::get('/cart', [CartController::class, 'index'])->name('cart.index');
    Route::post('/cart/add', [CartController::class, 'add'])->name('cart.add');
    Route::post('/cart/update', [CartController::class, 'update'])->name('cart.update');
    Route::post('/cart/remove', [CartController::class, 'remove'])->name('cart.remove');
    Route::post('/cart/clear', [CartController::class, 'clear'])->name('cart.clear');

    // Route untuk Checkout (Buyer)
    Route::get('/checkout', [CheckoutController::class, 'index'])->name('checkout.index');
    Route::post('/checkout/process', [CheckoutController::class, 'process'])->name('checkout.process');

    // Route untuk Payment (Buyer)
    Route::get('/payment/{transaction}', [PaymentController::class, 'show'])->name('payment.show');
    Route::post('/payment/{transaction}/process', [PaymentController::class, 'process'])->name('payment.process');

    // Route untuk Purchase History (Buyer)
    Route::get('/purchases', [PurchaseController::class, 'history'])->name('purchase.history');
    Route::get('/purchases/{transactionItem}/download', [PurchaseController::class, 'download'])->name('purchase.download');

    // Route untuk Face Scan (Buyer)
    Route::get('/face-scan', [FaceScanController::class, 'index'])->name('face-scan.index');
    Route::post('/face-scan/search', [FaceScanController::class, 'search'])
        ->middleware('throttle:10,1')
        ->name('face-scan.search');

    // Customer Face Search Routes
    Route::prefix('customer')->name('customer.')->middleware('auth')->group(function () {
        Route::get('/dashboard', [App\Http\Controllers\CustomerFaceSearchController::class, 'index'])->name('dashboard');
        Route::post('/search-albums', [App\Http\Controllers\CustomerFaceSearchController::class, 'searchAlbums'])->name('search-albums');
        Route::get('/album/{album}', [App\Http\Controllers\CustomerFaceSearchController::class, 'viewAlbum'])->name('view-album');
        Route::get('/album/{album}/all', [App\Http\Controllers\CustomerFaceSearchController::class, 'viewAllPhotos'])->name('view-all-photos');
    });

    // ─── Buyer Face Registration ───────────────────────────────────────────────
    // Accessible without having a face registered (so the buyer CAN register)
    Route::get('/buyer/register-face', [BuyerFaceRegistrationController::class, 'index'])
        ->name('buyer.register-face');
    Route::post('/buyer/register-face', [BuyerFaceRegistrationController::class, 'store'])
        ->name('buyer.register-face.store');

    // ─── Buyer Dashboard (Face Matcher) ────────────────────────────────────────
    // Requires face to be registered first (buyer-face middleware)
    Route::get('/buyer/dashboard', [BuyerDashboardController::class, 'index'])
        ->middleware('buyer-face')
        ->name('buyer.dashboard');

    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

// Admin routes
require __DIR__.'/admin.php';

require __DIR__.'/auth.php';
