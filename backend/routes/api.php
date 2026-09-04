<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

// Auth Controller
use App\Http\Controllers\Api\Auth\GoogleAuthController;

// Public & User Controllers
use App\Http\Controllers\Api\CategoryController;
use App\Http\Controllers\Api\ProductController;
use App\Http\Controllers\Api\ProductImageController;
use App\Http\Controllers\Api\AddressController;
use App\Http\Controllers\Api\CartController;
use App\Http\Controllers\Api\OrderController;
use App\Http\Controllers\Api\PaymentController;
use App\Http\Controllers\Api\WishlistController;

// Admin Controllers
use App\Http\Controllers\Api\Admin\AdminCategoryController;
use App\Http\Controllers\Api\Admin\AdminProductController;
use App\Http\Controllers\Api\Admin\AdminOrderController;
use App\Http\Controllers\Api\Admin\AdminCouponController;

/*
|--------------------------------------------------------------------------
| API Routes - Cloth Store
|--------------------------------------------------------------------------
*/

// ==========================================
// 1. AUTENTIKASI (PUBLIC & OAUTH)
// ==========================================
Route::prefix('auth')->group(function () {
    Route::get('/google/redirect', [GoogleAuthController::class, 'redirect']);
    Route::get('/google/callback', [GoogleAuthController::class, 'callback']);
});

// ==========================================
// 2. KATALOG & PRODUK (PUBLIC)
// ==========================================
Route::get('/categories', [CategoryController::class, 'index']);
Route::get('/categories/{slug}', [CategoryController::class, 'show']);

Route::get('/products', [ProductController::class, 'index']);
Route::get('/products/{slug}', [ProductController::class, 'show']);

// ==========================================
// 3. WEBHOOK MIDTRANS (PUBLIC)
// ==========================================
Route::post('/payment/notification', [PaymentController::class, 'notification']);

// ==========================================
// 4. RUTE TERPROTEKSI CUSTOMER (SANCTUM)
// ==========================================
Route::middleware('auth:sanctum')->group(function () {

    // --- User & Profile ---
    Route::get('/user', function (Request $request) {
        return $request->user();
    });
    
    Route::post('/logout', function (Request $request) {
        $request->user()->currentAccessToken()->delete();
        return response()->json(['message' => 'Logout berhasil']);
    });

    // --- Alamat (Addresses) ---
    Route::get('/addresses', [AddressController::class, 'index']);
    Route::post('/addresses', [AddressController::class, 'store']);
    Route::put('/addresses/{id}', [AddressController::class, 'update']);
    Route::delete('/addresses/{id}', [AddressController::class, 'destroy']);
    Route::patch('/addresses/{id}/set-default', [AddressController::class, 'setDefault']);

    // --- Keranjang Belanja (Cart) ---
    Route::get('/cart', [CartController::class, 'index']);
    Route::post('/cart/items', [CartController::class, 'store']);
    Route::put('/cart/items/{id}', [CartController::class, 'update']);
    Route::delete('/cart/items/{id}', [CartController::class, 'destroy']);

    // --- Pesanan (Orders) & Pembayaran (Payments) ---
    Route::get('/orders', [OrderController::class, 'index']);
    Route::post('/orders', [OrderController::class, 'store']);
    Route::get('/orders/{id}', [OrderController::class, 'show']);
    
    // Payment Snap Token
    Route::post('/orders/{orderId}/pay', [PaymentController::class, 'createSnapToken']);

    // --- Wishlist ---
    Route::get('/wishlists', [WishlistController::class, 'index']);
    Route::post('/wishlists/toggle', [WishlistController::class, 'toggle']);
});

// ==========================================
// 5. RUTE ADMIN (SANCTUM + ADMIN MIDDLEWARE)
// ==========================================
Route::middleware(['auth:sanctum', 'admin'])->prefix('admin')->group(function () {
    
    // Kelola Kategori
    Route::apiResource('categories', AdminCategoryController::class);

    // Kelola Produk & Gambar Produk
    Route::apiResource('products', AdminProductController::class)->except(['index', 'show']);
    Route::post('/products/{productId}/images', [ProductImageController::class, 'store']);
    Route::delete('/product-images/{id}', [ProductImageController::class, 'destroy']);

    // Kelola Pesanan
    Route::get('/orders', [AdminOrderController::class, 'index']);
    Route::get('/orders/{id}', [AdminOrderController::class, 'show']);
    Route::patch('/orders/{id}/status', [AdminOrderController::class, 'updateStatus']);

    // Kelola Kupon
    Route::apiResource('coupons', AdminCouponController::class)->except(['show', 'update']);
});