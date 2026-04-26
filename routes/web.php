<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\AlbumController;
use App\Http\Controllers\PhotoController;
use App\Http\Controllers\CatalogController;
use App\Http\Controllers\CartController;
use App\Http\Controllers\CheckoutController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\PurchaseController;
use App\Http\Controllers\FaceScanController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

// Public routes untuk catalog
Route::get('/catalog', [CatalogController::class, 'index'])->name('catalog.index');
Route::get('/catalog/{photo}', [CatalogController::class, 'show'])->name('catalog.show');

Route::get('/dashboard', [DashboardController::class, 'index'])->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    // Route untuk Album (Photographer)
    Route::get('/albums/create', [AlbumController::class, 'create'])->name('albums.create');
    Route::post('/albums', [AlbumController::class, 'store'])->name('albums.store');
    Route::get('/albums/{album}', [AlbumController::class, 'show'])->name('albums.show');

    // Route untuk Foto (Photographer)
    Route::get('/albums/{album}/photos/create', [PhotoController::class, 'create'])->name('photos.create');
    Route::post('/albums/{album}/photos', [PhotoController::class, 'store'])->name('photos.store');

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

    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});require __DIR__.'/auth.php';
