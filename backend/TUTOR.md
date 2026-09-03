# Cloth Store — Backend (Laravel)

Backend API untuk website Cloth Store menggunakan **Laravel**, **MySQL**, autentikasi via **Google OAuth (Login & Register)**, dan pembayaran via **Midtrans** (testing lokal menggunakan **ngrok** untuk webhook).

---

## 📋 Daftar Isi

1. [Requirement](#1-requirement)
2. [Instalasi Project Laravel](#2-instalasi-project-laravel)
3. [Setup Environment (.env) — MySQL](#3-setup-environment-env--mysql)
4. [Setup Database MySQL](#4-setup-database-mysql)
5. [Struktur Database & Migration](#5-struktur-database--migration)
6. [Setup Google OAuth (Login & Register)](#6-setup-google-oauth-login--register)
7. [Alur Autentikasi Google](#7-alur-autentikasi-google)
8. [Setup Midtrans (Payment)](#8-setup-midtrans-payment)
9. [Setup ngrok untuk Webhook Midtrans](#9-setup-ngrok-untuk-webhook-midtrans)
10. [Alur Payment (Checkout → Payment → Webhook)](#10-alur-payment-checkout--payment--webhook)
11. [Menjalankan Project](#11-menjalankan-project)
12. [Struktur Folder Penting](#12-struktur-folder-penting)
13. [Testing API (Ringkas)](#13-testing-api-ringkas)
14. [Troubleshooting](#14-troubleshooting)

---

## 1. Requirement

Pastikan sudah terinstall:

- PHP >= 8.2
- Composer
- MySQL >= 8.0
- Node.js & NPM (opsional, untuk asset jika perlu)
- ngrok (untuk expose localhost ke internet — dipakai webhook Midtrans)
- Akun Google Cloud Console (untuk kredensial OAuth)
- Akun Midtrans Sandbox (untuk kredensial payment)

---

## 2. Instalasi Project Laravel

```bash
# Buat project Laravel baru
composer create-project laravel/laravel cloth-store-backend

cd cloth-store-backend

# Install package untuk Google OAuth
composer require laravel/socialite

# Install package untuk Sanctum (API Token Auth, dipakai setelah login Google sukses)
composer require laravel/sanctum

# Publish config Sanctum
php artisan vendor:publish --provider="Laravel\Sanctum\SanctumServiceProvider"

# Install package Midtrans (PHP SDK resmi)
composer require midtrans/midtrans-php
```

---

## 3. Setup Environment (.env) — MySQL

Copy file `.env.example` menjadi `.env`:

```bash
cp .env.example .env
php artisan key:generate
```

Edit file `.env`, sesuaikan bagian berikut:

```env
APP_NAME="Cloth Store"
APP_ENV=local
APP_KEY=base64:xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx
APP_DEBUG=true
APP_URL=http://localhost:8000

# ==========================
# DATABASE (MYSQL)
# ==========================
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=cloth_store_db
DB_USERNAME=root
DB_PASSWORD=

# ==========================
# GOOGLE OAUTH
# ==========================
GOOGLE_CLIENT_ID=your-google-client-id.apps.googleusercontent.com
GOOGLE_CLIENT_SECRET=your-google-client-secret
GOOGLE_REDIRECT_URI=http://localhost:8000/api/auth/google/callback

# ==========================
# SANCTUM
# ==========================
SANCTUM_STATEFUL_DOMAINS=localhost:3000
SESSION_DOMAIN=localhost

# ==========================
# MIDTRANS
# ==========================
MIDTRANS_MERCHANT_ID=your-merchant-id
MIDTRANS_CLIENT_KEY=SB-Mid-client-xxxxxxxxxxxxxxxxxxx
MIDTRANS_SERVER_KEY=SB-Mid-server-xxxxxxxxxxxxxxxxxxx
MIDTRANS_IS_PRODUCTION=false
MIDTRANS_IS_SANITIZED=true
MIDTRANS_IS_3DS=true

# URL ngrok akan diisi setelah menjalankan ngrok (lihat poin 9)
MIDTRANS_NOTIFICATION_URL=https://xxxxxxxx.ngrok-free.app/api/payment/notification
```

> ⚠️ **Catatan:** `MIDTRANS_NOTIFICATION_URL` wajib memakai URL ngrok (bukan `localhost`) karena Midtrans perlu mengirim notifikasi status pembayaran ke server melalui internet publik.

---

## 4. Setup Database MySQL

Buat database secara manual via terminal MySQL atau tools seperti phpMyAdmin/TablePlus:

```sql
CREATE DATABASE cloth_store_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
```

Atau via terminal langsung:

```bash
mysql -u root -p -e "CREATE DATABASE cloth_store_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"
```

---

## 5. Struktur Database & Migration

Berikut daftar tabel yang dibutuhkan beserta relasinya (disusun berdasarkan kebutuhan e-commerce pakaian).

### 5.1 Daftar Tabel

| No | Tabel | Keterangan |
|----|-------|------------|
| 1  | `users` | Data user, termasuk `google_id` untuk OAuth |
| 2  | `addresses` | Alamat pengiriman milik user |
| 3  | `categories` | Kategori produk (self-relation untuk sub-kategori) |
| 4  | `products` | Data produk |
| 5  | `product_variants` | Varian produk (ukuran, warna, stok) |
| 6  | `product_images` | Gambar produk |
| 7  | `carts` | Keranjang belanja user |
| 8  | `cart_items` | Item dalam keranjang |
| 9  | `orders` | Data pesanan |
| 10 | `order_items` | Item dalam pesanan |
| 11 | `payments` | Data pembayaran (terhubung Midtrans) |
| 12 | `shipments` | Data pengiriman |
| 13 | `reviews` | Ulasan produk |
| 14 | `wishlists` | Produk favorit user |
| 15 | `coupons` | Kupon diskon |

### 5.2 Relasi Antar Tabel

| Relasi | Tipe |
|---|---|
| users → addresses | 1 - N |
| users → orders | 1 - N |
| users → carts | 1 - 1 |
| users → reviews | 1 - N |
| users → wishlists | 1 - N |
| categories → categories (self) | 1 - N (parent-child) |
| categories → products | 1 - N |
| products → product_variants | 1 - N |
| products → product_images | 1 - N |
| products → reviews | 1 - N |
| carts → cart_items | 1 - N |
| product_variants → cart_items | 1 - N |
| orders → order_items | 1 - N |
| product_variants → order_items | 1 - N |
| orders → payments | 1 - 1 (atau 1-N jika ada retry) |
| orders → shipments | 1 - 1 |
| orders → coupons | N - 1 (opsional) |

### 5.3 Membuat Migration

Jalankan perintah berikut satu per satu (urutan penting karena foreign key):

```bash
php artisan make:migration create_users_table_custom
php artisan make:migration create_addresses_table
php artisan make:migration create_categories_table
php artisan make:migration create_products_table
php artisan make:migration create_product_variants_table
php artisan make:migration create_product_images_table
php artisan make:migration create_carts_table
php artisan make:migration create_cart_items_table
php artisan make:migration create_coupons_table
php artisan make:migration create_orders_table
php artisan make:migration create_order_items_table
php artisan make:migration create_payments_table
php artisan make:migration create_shipments_table
php artisan make:migration create_reviews_table
php artisan make:migration create_wishlists_table
```

> 📌 Catatan: migration bawaan Laravel `create_users_table` sudah ada. Modifikasi langsung file migration `users` bawaan untuk menambahkan kolom `google_id`, `avatar`, `phone`, `role`, dan membuat `password` nullable (karena user Google Auth tidak punya password).

### 5.4 Isi Migration Utama

**`database/migrations/xxxx_xx_xx_000000_create_users_table.php`** (edit migration bawaan):

```php
Schema::create('users', function (Blueprint $table) {
    $table->id();
    $table->string('name');
    $table->string('email')->unique();
    $table->string('google_id')->nullable()->unique();
    $table->string('avatar')->nullable();
    $table->timestamp('email_verified_at')->nullable();
    $table->string('password')->nullable(); // nullable karena login via Google
    $table->string('phone')->nullable();
    $table->enum('role', ['customer', 'admin'])->default('customer');
    $table->rememberToken();
    $table->timestamps();
});
```

**`create_addresses_table`:**

```php
Schema::create('addresses', function (Blueprint $table) {
    $table->id();
    $table->foreignId('user_id')->constrained()->onDelete('cascade');
    $table->string('label')->nullable(); // rumah, kantor, dll
    $table->string('recipient_name');
    $table->string('phone');
    $table->text('address');
    $table->string('city');
    $table->string('province');
    $table->string('postal_code');
    $table->boolean('is_default')->default(false);
    $table->timestamps();
});
```

**`create_categories_table`:**

```php
Schema::create('categories', function (Blueprint $table) {
    $table->id();
    $table->string('name');
    $table->string('slug')->unique();
    $table->foreignId('parent_id')->nullable()->constrained('categories')->onDelete('cascade');
    $table->timestamps();
});
```

**`create_products_table`:**

```php
Schema::create('products', function (Blueprint $table) {
    $table->id();
    $table->foreignId('category_id')->constrained()->onDelete('cascade');
    $table->string('name');
    $table->string('slug')->unique();
    $table->text('description')->nullable();
    $table->decimal('base_price', 12, 2);
    $table->string('brand')->nullable();
    $table->enum('gender', ['pria', 'wanita', 'unisex'])->default('unisex');
    $table->boolean('is_active')->default(true);
    $table->timestamps();
});
```

**`create_product_variants_table`:**

```php
Schema::create('product_variants', function (Blueprint $table) {
    $table->id();
    $table->foreignId('product_id')->constrained()->onDelete('cascade');
    $table->string('size');
    $table->string('color');
    $table->string('sku')->unique();
    $table->decimal('price', 12, 2);
    $table->integer('stock_quantity')->default(0);
    $table->timestamps();
});
```

**`create_product_images_table`:**

```php
Schema::create('product_images', function (Blueprint $table) {
    $table->id();
    $table->foreignId('product_id')->constrained()->onDelete('cascade');
    $table->string('image_url');
    $table->boolean('is_primary')->default(false);
    $table->integer('sort_order')->default(0);
    $table->timestamps();
});
```

**`create_carts_table`:**

```php
Schema::create('carts', function (Blueprint $table) {
    $table->id();
    $table->foreignId('user_id')->constrained()->onDelete('cascade');
    $table->timestamps();
});
```

**`create_cart_items_table`:**

```php
Schema::create('cart_items', function (Blueprint $table) {
    $table->id();
    $table->foreignId('cart_id')->constrained()->onDelete('cascade');
    $table->foreignId('product_variant_id')->constrained()->onDelete('cascade');
    $table->integer('quantity')->default(1);
    $table->timestamps();
});
```

**`create_coupons_table`:**

```php
Schema::create('coupons', function (Blueprint $table) {
    $table->id();
    $table->string('code')->unique();
    $table->enum('discount_type', ['percentage', 'fixed']);
    $table->decimal('discount_value', 12, 2);
    $table->decimal('min_purchase', 12, 2)->default(0);
    $table->timestamp('expired_at')->nullable();
    $table->timestamps();
});
```

**`create_orders_table`:**

```php
Schema::create('orders', function (Blueprint $table) {
    $table->id();
    $table->foreignId('user_id')->constrained()->onDelete('cascade');
    $table->foreignId('address_id')->constrained()->onDelete('restrict');
    $table->foreignId('coupon_id')->nullable()->constrained()->onDelete('set null');
    $table->string('order_number')->unique();
    $table->enum('status', ['pending', 'paid', 'shipped', 'completed', 'cancelled'])->default('pending');
    $table->decimal('total_amount', 12, 2);
    $table->timestamps();
});
```

**`create_order_items_table`:**

```php
Schema::create('order_items', function (Blueprint $table) {
    $table->id();
    $table->foreignId('order_id')->constrained()->onDelete('cascade');
    $table->foreignId('product_variant_id')->constrained()->onDelete('restrict');
    $table->integer('quantity');
    $table->decimal('price_at_purchase', 12, 2); // snapshot harga saat transaksi
    $table->timestamps();
});
```

**`create_payments_table`:**

```php
Schema::create('payments', function (Blueprint $table) {
    $table->id();
    $table->foreignId('order_id')->constrained()->onDelete('cascade');
    $table->string('payment_method')->nullable(); // gopay, bank_transfer, credit_card, dll (dari Midtrans)
    $table->decimal('amount', 12, 2);
    $table->enum('status', ['pending', 'settlement', 'expire', 'cancel', 'deny', 'refund'])->default('pending');
    $table->string('transaction_id')->nullable(); // dari Midtrans
    $table->string('snap_token')->nullable(); // token Snap Midtrans
    $table->timestamp('paid_at')->nullable();
    $table->timestamps();
});
```

**`create_shipments_table`:**

```php
Schema::create('shipments', function (Blueprint $table) {
    $table->id();
    $table->foreignId('order_id')->constrained()->onDelete('cascade');
    $table->string('courier')->nullable();
    $table->string('tracking_number')->nullable();
    $table->enum('status', ['pending', 'processing', 'shipped', 'delivered'])->default('pending');
    $table->timestamp('shipped_at')->nullable();
    $table->timestamps();
});
```

**`create_reviews_table`:**

```php
Schema::create('reviews', function (Blueprint $table) {
    $table->id();
    $table->foreignId('product_id')->constrained()->onDelete('cascade');
    $table->foreignId('user_id')->constrained()->onDelete('cascade');
    $table->tinyInteger('rating'); // 1-5
    $table->text('comment')->nullable();
    $table->timestamps();
});
```

**`create_wishlists_table`:**

```php
Schema::create('wishlists', function (Blueprint $table) {
    $table->id();
    $table->foreignId('user_id')->constrained()->onDelete('cascade');
    $table->foreignId('product_id')->constrained()->onDelete('cascade');
    $table->timestamps();
    $table->unique(['user_id', 'product_id']); // biar tidak duplikat
});
```

### 5.5 Jalankan Migration

```bash
php artisan migrate
```

Jika ingin reset total (hati-hati, akan menghapus semua data):

```bash
php artisan migrate:fresh
```

---

## 6. Setup Google OAuth (Login & Register)

### 6.1 Buat Kredensial di Google Cloud Console

1. Buka [Google Cloud Console](https://console.cloud.google.com/)
2. Buat project baru (atau pilih project yang sudah ada)
3. Masuk ke **APIs & Services → OAuth consent screen**, isi data aplikasi (nama, email support, dll), pilih **External** jika untuk publik
4. Masuk ke **APIs & Services → Credentials → Create Credentials → OAuth Client ID**
5. Pilih **Application type: Web application**
6. Isi **Authorized redirect URIs**:
   ```
   http://localhost:8000/api/auth/google/callback
   ```
7. Klik **Create**, salin **Client ID** dan **Client Secret**
8. Masukkan ke `.env` sesuai poin 3 di atas

### 6.2 Konfigurasi `config/services.php`

Tambahkan konfigurasi Google:

```php
'google' => [
    'client_id' => env('GOOGLE_CLIENT_ID'),
    'client_secret' => env('GOOGLE_CLIENT_SECRET'),
    'redirect' => env('GOOGLE_REDIRECT_URI'),
],
```

### 6.3 Buat Controller Auth

```bash
php artisan make:controller Api/Auth/GoogleAuthController
```

**`app/Http/Controllers/Api/Auth/GoogleAuthController.php`:**

```php
<?php

namespace App\Http\Controllers\Api\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Cart;
use Illuminate\Support\Str;
use Laravel\Socialite\Facades\Socialite;

class GoogleAuthController extends Controller
{
    // Redirect ke halaman login Google
    public function redirect()
    {
        return Socialite::driver('google')
            ->stateless()
            ->redirect();
    }

    // Callback setelah user login/register via Google
    public function callback()
    {
        $googleUser = Socialite::driver('google')->stateless()->user();

        // Cek apakah user sudah pernah daftar (login) atau belum (register otomatis)
        $user = User::where('google_id', $googleUser->getId())
            ->orWhere('email', $googleUser->getEmail())
            ->first();

        if (!$user) {
            // REGISTER otomatis untuk user baru
            $user = User::create([
                'name' => $googleUser->getName(),
                'email' => $googleUser->getEmail(),
                'google_id' => $googleUser->getId(),
                'avatar' => $googleUser->getAvatar(),
                'password' => bcrypt(Str::random(24)), // password random, tidak dipakai
                'role' => 'customer',
                'email_verified_at' => now(),
            ]);

            // Buat cart kosong otomatis untuk user baru
            Cart::create(['user_id' => $user->id]);
        } else if (!$user->google_id) {
            // Kalau email sudah ada tapi belum terhubung Google, hubungkan
            $user->update([
                'google_id' => $googleUser->getId(),
                'avatar' => $googleUser->getAvatar(),
            ]);
        }

        // Generate token Sanctum untuk autentikasi API selanjutnya
        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'message' => 'Login berhasil',
            'user' => $user,
            'token' => $token,
        ]);
    }
}
```

### 6.4 Tambahkan Route

**`routes/api.php`:**

```php
use App\Http\Controllers\Api\Auth\GoogleAuthController;

// ==========================
// AUTH — GOOGLE OAUTH
// ==========================
Route::get('/auth/google/redirect', [GoogleAuthController::class, 'redirect']);
Route::get('/auth/google/callback', [GoogleAuthController::class, 'callback']);

// Route yang butuh login (contoh)
Route::middleware('auth:sanctum')->group(function () {
    Route::get('/user', function (Illuminate\Http\Request $request) {
        return $request->user();
    });
    Route::post('/logout', function (Illuminate\Http\Request $request) {
        $request->user()->currentAccessToken()->delete();
        return response()->json(['message' => 'Logout berhasil']);
    });
});
```

---

## 7. Alur Autentikasi Google

```
User klik "Login with Google" (dari frontend)
        ↓
Frontend redirect ke: GET /api/auth/google/redirect
        ↓
Laravel redirect ke halaman consent Google
        ↓
User pilih akun & izinkan akses
        ↓
Google redirect balik ke: GET /api/auth/google/callback
        ↓
Laravel cek: user baru? → REGISTER otomatis
             user lama? → LOGIN, ambil data
        ↓
Laravel generate Sanctum Token
        ↓
Return JSON { user, token } ke frontend
        ↓
Frontend simpan token, dipakai untuk request selanjutnya
(Header: Authorization: Bearer <token>)
```

> ✅ Dengan pola ini, **satu alur Google OAuth otomatis menangani Login dan Register** — tidak perlu form register/login manual terpisah.

---

## 8. Setup Midtrans (Payment)

### 8.1 Daftar Akun Sandbox

1. Daftar di [https://dashboard.sandbox.midtrans.com/register](https://dashboard.sandbox.midtrans.com/register)
2. Setelah login, masuk ke **Settings → Access Keys**
3. Salin **Merchant ID**, **Client Key**, dan **Server Key**
4. Masukkan ke `.env` sesuai poin 3

### 8.2 Konfigurasi Config Midtrans

Buat file `config/midtrans.php`:

```php
<?php

return [
    'merchant_id' => env('MIDTRANS_MERCHANT_ID'),
    'client_key' => env('MIDTRANS_CLIENT_KEY'),
    'server_key' => env('MIDTRANS_SERVER_KEY'),
    'is_production' => env('MIDTRANS_IS_PRODUCTION', false),
    'is_sanitized' => env('MIDTRANS_IS_SANITIZED', true),
    'is_3ds' => env('MIDTRANS_IS_3DS', true),
];
```

### 8.3 Buat Service Midtrans

```bash
mkdir -p app/Services
```

**`app/Services/MidtransService.php`:**

```php
<?php

namespace App\Services;

use Midtrans\Config;
use Midtrans\Snap;
use Midtrans\Notification;

class MidtransService
{
    public function __construct()
    {
        Config::$serverKey = config('midtrans.server_key');
        Config::$isProduction = config('midtrans.is_production');
        Config::$isSanitized = config('midtrans.is_sanitized');
        Config::$is3ds = config('midtrans.is_3ds');
    }

    // Membuat transaksi & mendapatkan Snap Token
    public function createTransaction($order)
    {
        $params = [
            'transaction_details' => [
                'order_id' => $order->order_number,
                'gross_amount' => (int) $order->total_amount,
            ],
            'customer_details' => [
                'first_name' => $order->user->name,
                'email' => $order->user->email,
                'phone' => $order->address->phone,
            ],
            'item_details' => $order->orderItems->map(function ($item) {
                return [
                    'id' => $item->product_variant_id,
                    'price' => (int) $item->price_at_purchase,
                    'quantity' => $item->quantity,
                    'name' => $item->productVariant->product->name . ' (' . $item->productVariant->size . '/' . $item->productVariant->color . ')',
                ];
            })->toArray(),
        ];

        return Snap::getSnapToken($params);
    }

    // Menangkap notifikasi dari webhook Midtrans
    public function handleNotification()
    {
        return new Notification();
    }
}
```

### 8.4 Buat Controller Payment

```bash
php artisan make:controller Api/PaymentController
```

**`app/Http/Controllers/Api/PaymentController.php`:**

```php
<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Payment;
use App\Services\MidtransService;
use Illuminate\Http\Request;

class PaymentController extends Controller
{
    protected $midtransService;

    public function __construct(MidtransService $midtransService)
    {
        $this->midtransService = $midtransService;
    }

    // Buat Snap Token untuk order tertentu
    public function createSnapToken(Request $request, $orderId)
    {
        $order = Order::with(['user', 'address', 'orderItems.productVariant.product'])
            ->findOrFail($orderId);

        $snapToken = $this->midtransService->createTransaction($order);

        // Simpan/update record payment dengan snap_token
        Payment::updateOrCreate(
            ['order_id' => $order->id],
            [
                'amount' => $order->total_amount,
                'status' => 'pending',
                'snap_token' => $snapToken,
            ]
        );

        return response()->json([
            'snap_token' => $snapToken,
            'client_key' => config('midtrans.client_key'),
        ]);
    }

    // Endpoint webhook — dipanggil Midtrans via ngrok URL
    public function notification(Request $request)
    {
        $notif = $this->midtransService->handleNotification();

        $orderNumber = $notif->order_id;
        $transactionStatus = $notif->transaction_status;
        $fraudStatus = $notif->fraud_status ?? null;
        $paymentType = $notif->payment_type;
        $transactionId = $notif->transaction_id;

        $order = Order::where('order_number', $orderNumber)->firstOrFail();
        $payment = Payment::where('order_id', $order->id)->firstOrFail();

        if ($transactionStatus == 'capture') {
            if ($fraudStatus == 'challenge') {
                $payment->status = 'pending';
            } else if ($fraudStatus == 'accept') {
                $payment->status = 'settlement';
                $order->status = 'paid';
            }
        } else if ($transactionStatus == 'settlement') {
            $payment->status = 'settlement';
            $order->status = 'paid';
        } else if (in_array($transactionStatus, ['cancel', 'deny', 'expire'])) {
            $payment->status = $transactionStatus;
            $order->status = 'cancelled';
        } else if ($transactionStatus == 'pending') {
            $payment->status = 'pending';
        }

        $payment->payment_method = $paymentType;
        $payment->transaction_id = $transactionId;
        $payment->paid_at = $payment->status == 'settlement' ? now() : null;
        $payment->save();
        $order->save();

        return response()->json(['message' => 'Notifikasi berhasil diproses']);
    }
}
```

### 8.5 Tambahkan Route Payment

**`routes/api.php`:**

```php
use App\Http\Controllers\Api\PaymentController;

Route::middleware('auth:sanctum')->group(function () {
    Route::post('/orders/{orderId}/pay', [PaymentController::class, 'createSnapToken']);
});

// Route notifikasi TIDAK pakai middleware auth (Midtrans yang akses langsung dari server mereka)
Route::post('/payment/notification', [PaymentController::class, 'notification']);
```

> ⚠️ Route `payment/notification` harus dikecualikan dari **CSRF/Sanctum middleware** karena diakses langsung oleh server Midtrans, bukan dari browser user. Karena ini di bawah `routes/api.php`, secara default sudah aman dari CSRF (Laravel API route tidak pakai CSRF).

---

## 9. Setup ngrok untuk Webhook Midtrans

Midtrans butuh URL publik untuk mengirim notifikasi pembayaran ke server lokal kita. Di sinilah ngrok berperan.

### 9.1 Install ngrok

Download dari [https://ngrok.com/download](https://ngrok.com/download), lalu login/daftar akun dan authenticate:

```bash
ngrok config add-authtoken <TOKEN_DARI_DASHBOARD_NGROK>
```

### 9.2 Jalankan Laravel

```bash
php artisan serve
```

Laravel akan berjalan di `http://localhost:8000`.

### 9.3 Jalankan ngrok

Di terminal terpisah:

```bash
ngrok http 8000
```

Akan muncul output seperti:

```
Forwarding    https://a1b2c3d4e5f6.ngrok-free.app -> http://localhost:8000
```

Salin URL `https://a1b2c3d4e5f6.ngrok-free.app` tersebut.

### 9.4 Daftarkan URL ke Midtrans Dashboard

1. Buka [Midtrans Dashboard Sandbox](https://dashboard.sandbox.midtrans.com/)
2. Masuk ke **Settings → Configuration**
3. Isi **Payment Notification URL** dengan:
   ```
   https://a1b2c3d4e5f6.ngrok-free.app/api/payment/notification
   ```
4. Simpan

### 9.5 Update `.env`

```env
MIDTRANS_NOTIFICATION_URL=https://a1b2c3d4e5f6.ngrok-free.app/api/payment/notification
```

> ⚠️ **Penting:** URL ngrok versi gratis akan **berubah setiap kali restart ngrok**. Jangan lupa update kembali di Midtrans Dashboard & `.env` setiap kali menjalankan ulang ngrok saat development.

---

## 10. Alur Payment (Checkout → Payment → Webhook)

```
User checkout dari cart → sistem buat record "orders" (status: pending)
        ↓
Frontend request: POST /api/orders/{orderId}/pay
        ↓
Backend generate Snap Token via Midtrans API
        ↓
Backend simpan record "payments" (status: pending, snap_token: xxx)
        ↓
Frontend tampilkan Snap popup Midtrans (pakai snap_token & client_key)
        ↓
User bayar (transfer bank, e-wallet, kartu kredit, dll)
        ↓
Midtrans kirim notifikasi ke webhook: POST https://xxx.ngrok-free.app/api/payment/notification
        ↓
Backend proses notifikasi:
   - update "payments".status → settlement/expire/cancel
   - update "orders".status → paid/cancelled
        ↓
Frontend bisa polling atau cek status order untuk update tampilan
```

---

## 11. Menjalankan Project

Jalankan 2 terminal terpisah untuk development:

**Terminal 1 — Laravel server:**

```bash
php artisan serve
```

**Terminal 2 — ngrok (khusus saat testing payment):**

```bash
ngrok http 8000
```

Pastikan juga sudah menjalankan:

```bash
php artisan migrate
```

---

## 12. Struktur Folder Penting

```
app/
├── Http/
│   └── Controllers/
│       └── Api/
│           ├── Auth/
│           │   └── GoogleAuthController.php
│           └── PaymentController.php
├── Models/
│   ├── User.php
│   ├── Address.php
│   ├── Category.php
│   ├── Product.php
│   ├── ProductVariant.php
│   ├── ProductImage.php
│   ├── Cart.php
│   ├── CartItem.php
│   ├── Order.php
│   ├── OrderItem.php
│   ├── Payment.php
│   ├── Shipment.php
│   ├── Review.php
│   ├── Wishlist.php
│   └── Coupon.php
└── Services/
    └── MidtransService.php

config/
├── services.php     (kredensial Google)
└── midtrans.php     (kredensial Midtrans)

database/
└── migrations/
    └── (15 file migration tabel di atas)

routes/
└── api.php
```

---

## 13. Testing API (Ringkas)

**Login/Register via Google** (buka langsung di browser, bukan Postman, karena melibatkan redirect):

```
http://localhost:8000/api/auth/google/redirect
```

**Cek user login** (pakai Postman/Insomnia, isi header `Authorization: Bearer <token>`):

```
GET http://localhost:8000/api/user
```

**Buat Snap Token pembayaran:**

```
POST http://localhost:8000/api/orders/1/pay
Authorization: Bearer <token>
```

**Simulasi webhook Midtrans** (biasanya otomatis terpanggil setelah pembayaran sandbox berhasil, tapi bisa juga dites manual dari Postman ke URL ngrok):

```
POST https://xxxx.ngrok-free.app/api/payment/notification
```

---

## 14. Troubleshooting

| Masalah | Solusi |
|---|---|
| `SQLSTATE[HY000] [1045] Access denied` | Cek ulang `DB_USERNAME` & `DB_PASSWORD` di `.env` |
| Redirect Google error `redirect_uri_mismatch` | Pastikan URL di Google Console **persis sama** dengan `GOOGLE_REDIRECT_URI` di `.env` |
| Webhook Midtrans tidak masuk ke Laravel | Pastikan ngrok masih berjalan & URL di Midtrans Dashboard sudah yang terbaru (URL ngrok berubah tiap restart) |
| `Class "Midtrans\Config" not found` | Jalankan ulang `composer require midtrans/midtrans-php` lalu `composer dump-autoload` |
| Token Sanctum tidak terbaca di request | Pastikan header `Authorization: Bearer <token>` terkirim, dan route berada di dalam middleware `auth:sanctum` |
| CORS error dari frontend | Install & konfigurasi `fruitcake/laravel-cors` atau sesuaikan `config/cors.php` bawaan Laravel 11 |

---

## Ringkasan Environment Variable Lengkap

```env
APP_NAME="Cloth Store"
APP_ENV=local
APP_KEY=
APP_DEBUG=true
APP_URL=http://localhost:8000

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=cloth_store_db
DB_USERNAME=root
DB_PASSWORD=

GOOGLE_CLIENT_ID=
GOOGLE_CLIENT_SECRET=
GOOGLE_REDIRECT_URI=http://localhost:8000/api/auth/google/callback

SANCTUM_STATEFUL_DOMAINS=localhost:3000
SESSION_DOMAIN=localhost

MIDTRANS_MERCHANT_ID=
MIDTRANS_CLIENT_KEY=
MIDTRANS_SERVER_KEY=
MIDTRANS_IS_PRODUCTION=false
MIDTRANS_IS_SANITIZED=true
MIDTRANS_IS_3DS=true
MIDTRANS_NOTIFICATION_URL=
```
