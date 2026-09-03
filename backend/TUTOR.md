Dokumen panduan (`tutor.md`) telah dirapikan agar lebih terstruktur, konsisten, dan nyaman dibaca. Seluruh istilah teknis tetap dipertahankan, sementara narasi serta penjelasan disajikan dalam bahasa Indonesia yang baku, jelas, dan profesional.

---

# Cloth Store — Panduan Integrasi Backend (Laravel)

Dokumen ini berisi panduan langkah demi langkah untuk membangun dan mengonfigurasi Backend API platform *e-commerce* **Cloth Store**. Dokumentasi ini mencakup integrasi **Laravel**, **MySQL**, **Google OAuth** untuk autentikasi, serta **Midtrans** (menggunakan **ngrok** untuk pengujian *webhook* lokal) untuk sistem pembayaran.

---

## 📋 Daftar Isi

1. [Prasyarat Sistem](https://www.google.com/search?q=%231-prasyarat-sistem)
2. [Instalasi Proyek Laravel](https://www.google.com/search?q=%232-instalasi-proyek-laravel)
3. [Konfigurasi Lingkungan (.env) — MySQL](https://www.google.com/search?q=%233-konfigurasi-lingkungan-env--mysql)
4. [Inisialisasi Database MySQL](https://www.google.com/search?q=%234-inisialisasi-database-mysql)
5. [Skema Database & Migrasi](https://www.google.com/search?q=%235-skema-database--migrasi)
6. [Konfigurasi Google OAuth](https://www.google.com/search?q=%236-konfigurasi-google-oauth)
7. [Alur Autentikasi Google](https://www.google.com/search?q=%237-alur-autentikasi-google)
8. [Konfigurasi Midtrans Payment Gateway](https://www.google.com/search?q=%238-konfigurasi-midtrans-payment-gateway)
9. [Konfigurasi ngrok untuk Webhook Midtrans](https://www.google.com/search?q=%239-konfigurasi-ngrok-untuk-webhook-midtrans)
10. [Alur Transaksi & Pembayaran](https://www.google.com/search?q=%2310-alur-transaksi--pembayaran)
11. [Petunjuk Menjalankan Aplikasi](https://www.google.com/search?q=%2311-petunjuk-menjalankan-aplikasi)
12. [Struktur Direktori Penting](https://www.google.com/search?q=%2312-struktur-direktori-penting)
13. [Panduan Pengujian API](https://www.google.com/search?q=%2313-panduan-pengujian-api)
14. [Penanganan Masalah (Troubleshooting)](https://www.google.com/search?q=%2314-penanganan-masalah-troubleshooting)

---

## 1. Prasyarat Sistem

Sebelum memulai, pastikan perangkat pengembangan Anda telah terpasang dependensi berikut:

* **PHP** >= 8.2
* **Composer** (Manajer paket PHP)
* **MySQL** >= 8.0
* **Node.js & NPM** (Opsional, untuk kompilasi *asset*)
* **ngrok** (Perkakas *tunneling* untuk mengekspos *server* lokal ke internet)
* **Akun Google Cloud Console** (Untuk mendapatkan kredensial Google OAuth)
* **Akun Midtrans Sandbox** (Untuk pengujian *payment gateway*)

---

## 2. Instalasi Proyek Laravel

Jalankan perintah berikut pada terminal Anda untuk membuat proyek baru dan menginstal pustaka (*package*) yang dibutuhkan:

```bash
# 1. Buat proyek Laravel baru
composer create-project laravel/laravel cloth-store-backend

# 2. Masuk ke direktori proyek
cd cloth-store-backend

# 3. Instal Laravel Socialite (Untuk Google OAuth)
composer require laravel/socialite

# 4. Instal Laravel Sanctum (Autentikasi API berbasis Token)
composer require laravel/sanctum

# 5. Publikasikan berkas konfigurasi Sanctum
php artisan vendor:publish --provider="Laravel\Sanctum\SanctumServiceProvider"

# 6. Instal SDK Resmi Midtrans PHP
composer require midtrans/midtrans-php

```

---

## 3. Konfigurasi Lingkungan (.env) — MySQL

Duplikasi berkas `.env.example` menjadi `.env`, lalu buat *application key* baru:

```bash
cp .env.example .env
php artisan key:generate

```

Buka berkas `.env` dan sesuaikan nilainya dengan konfigurasi berikut:

```env
APP_NAME="Cloth Store"
APP_ENV=local
APP_KEY=base64:xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx
APP_DEBUG=true
APP_URL=http://localhost:8000

# ==========================================
# KONFIGURASI DATABASE (MYSQL)
# ==========================================
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=cloth_store_db
DB_USERNAME=root
DB_PASSWORD=

# ==========================================
# KONFIGURASI GOOGLE OAUTH
# ==========================================
GOOGLE_CLIENT_ID=your-google-client-id.apps.googleusercontent.com
GOOGLE_CLIENT_SECRET=your-google-client-secret
GOOGLE_REDIRECT_URI=http://localhost:8000/api/auth/google/callback

# ==========================================
# KONFIGURASI SANCTUM
# ==========================================
SANCTUM_STATEFUL_DOMAINS=localhost:3000
SESSION_DOMAIN=localhost

# ==========================================
# KONFIGURASI MIDTRANS
# ==========================================
MIDTRANS_MERCHANT_ID=your-merchant-id
MIDTRANS_CLIENT_KEY=SB-Mid-client-xxxxxxxxxxxxxxxxxxx
MIDTRANS_SERVER_KEY=SB-Mid-server-xxxxxxxxxxxxxxxxxxx
MIDTRANS_IS_PRODUCTION=false
MIDTRANS_IS_SANITIZED=true
MIDTRANS_IS_3DS=true

# URL Notifikasi ngrok (Diperbarui setelah ngrok dijalankan pada Poin 9)
MIDTRANS_NOTIFICATION_URL=https://xxxxxxxx.ngrok-free.app/api/payment/notification

```

> ⚠️ **Catatan Penting:** Variable `MIDTRANS_NOTIFICATION_URL` wajib menggunakan URL publik (seperti URL dari ngrok), karena Midtrans membutuhkan akses publik untuk mengirimkan notifikasi *webhook* status transaksi.

---

## 4. Inisialisasi Database MySQL

Buat basis data baru bernama `cloth_store_db` melalui perintah SQL atau perkakas manajemen database pilihan Anda (seperti phpMyAdmin, TablePlus, atau DBeaver):

```sql
CREATE DATABASE cloth_store_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

```

Atau langsung dari terminal:

```bash
mysql -u root -p -e "CREATE DATABASE cloth_store_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"

```

---

## 5. Skema Database & Migrasi

### 5.1 Daftar Tabel Sistem

| No | Tabel | Deskripsi |
| --- | --- | --- |
| 1 | `users` | Menyimpan data pengguna, termasuk `google_id` |
| 2 | `addresses` | Menyimpan alamat pengiriman milik pengguna |
| 3 | `categories` | Kategori produk (*self-referencing* untuk sub-kategori) |
| 4 | `products` | Data utama produk |
| 5 | `product_variants` | Varian spesifik produk (ukuran, warna, stok) |
| 6 | `product_images` | Berkas gambar pendukung produk |
| 7 | `carts` | Keranjang belanja pengguna |
| 8 | `cart_items` | Item-item di dalam keranjang belanja |
| 9 | `orders` | Transaksi pemesanan |
| 10 | `order_items` | Detail barang yang dipesan |
| 11 | `payments` | Catatan transaksi pembayaran (Midtrans) |
| 12 | `shipments` | Data dan status pengiriman barang |
| 13 | `reviews` | Ulasan produk dari pengguna |
| 14 | `wishlists` | Daftar produk favorit pengguna |
| 15 | `coupons` | Data kupon atau kode promo |

### 5.2 Hubungan Antar Tabel (Relasi)

| Relasi | Tipe Hubungan |
| --- | --- |
| `users` → `addresses` | One to Many ($1 : N$) |
| `users` → `orders` | One to Many ($1 : N$) |
| `users` → `carts` | One to One ($1 : 1$) |
| `users` → `reviews` | One to Many ($1 : N$) |
| `users` → `wishlists` | One to Many ($1 : N$) |
| `categories` → `categories` | One to Many ($1 : N$, *Parent-Child*) |
| `categories` → `products` | One to Many ($1 : N$) |
| `products` → `product_variants` | One to Many ($1 : N$) |
| `products` → `product_images` | One to Many ($1 : N$) |
| `products` → `reviews` | One to Many ($1 : N$) |
| `carts` → `cart_items` | One to Many ($1 : N$) |
| `product_variants` → `cart_items` | One to Many ($1 : N$) |
| `orders` → `order_items` | One to Many ($1 : N$) |
| `product_variants` → `order_items` | One to Many ($1 : N$) |
| `orders` → `payments` | One to One ($1 : 1$) |
| `orders` → `shipments` | One to One ($1 : 1$) |
| `orders` → `coupons` | Many to One ($N : 1$, *Opsional*) |

### 5.3 Membuat Berkas Migrasi

Jalankan perintah pembuatan migrasi berikut secara berurutan untuk menjaga dependensi *foreign key*:

```bash
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

> 📌 **Catatan:** Jangan buat migrasi tabel `users` baru, melainkan perbarui berkas migrasi bawaan Laravel `database/migrations/xxxx_xx_xx_xxxxxx_create_users_table.php`.

### 5.4 Kode Sumber Berkas Migrasi

**1. `database/migrations/xxxx_xx_xx_000000_create_users_table.php**`

```php
Schema::create('users', function (Blueprint $table) {
    $table->id();
    $table->string('name');
    $table->string('email')->unique();
    $table->string('google_id')->nullable()->unique();
    $table->string('avatar')->nullable();
    $table->timestamp('email_verified_at')->nullable();
    $table->string('password')->nullable(); // Nullable karena Login menggunakan Google OAuth
    $table->string('phone')->nullable();
    $table->enum('role', ['customer', 'admin'])->default('customer');
    $table->rememberToken();
    $table->timestamps();
});

```

**2. `create_addresses_table**`

```php
Schema::create('addresses', function (Blueprint $table) {
    $table->id();
    $table->foreignId('user_id')->constrained()->onDelete('cascade');
    $table->string('label')->nullable(); // Contoh: Rumah, Kantor
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

**3. `create_categories_table**`

```php
Schema::create('categories', function (Blueprint $table) {
    $table->id();
    $table->string('name');
    $table->string('slug')->unique();
    $table->foreignId('parent_id')->nullable()->constrained('categories')->onDelete('cascade');
    $table->timestamps();
});

```

**4. `create_products_table**`

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

**5. `create_product_variants_table**`

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

**6. `create_product_images_table**`

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

**7. `create_carts_table**`

```php
Schema::create('carts', function (Blueprint $table) {
    $table->id();
    $table->foreignId('user_id')->constrained()->onDelete('cascade');
    $table->timestamps();
});

```

**8. `create_cart_items_table**`

```php
Schema::create('cart_items', function (Blueprint $table) {
    $table->id();
    $table->foreignId('cart_id')->constrained()->onDelete('cascade');
    $table->foreignId('product_variant_id')->constrained()->onDelete('cascade');
    $table->integer('quantity')->default(1);
    $table->timestamps();
});

```

**9. `create_coupons_table**`

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

**10. `create_orders_table**`

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

**11. `create_order_items_table**`

```php
Schema::create('order_items', function (Blueprint $table) {
    $table->id();
    $table->foreignId('order_id')->constrained()->onDelete('cascade');
    $table->foreignId('product_variant_id')->constrained()->onDelete('restrict');
    $table->integer('quantity');
    $table->decimal('price_at_purchase', 12, 2); // Menyimpan histori harga saat barang dibeli
    $table->timestamps();
});

```

**12. `create_payments_table**`

```php
Schema::create('payments', function (Blueprint $table) {
    $table->id();
    $table->foreignId('order_id')->constrained()->onDelete('cascade');
    $table->string('payment_method')->nullable(); // gopay, bank_transfer, credit_card, dll.
    $table->decimal('amount', 12, 2);
    $table->enum('status', ['pending', 'settlement', 'expire', 'cancel', 'deny', 'refund'])->default('pending');
    $table->string('transaction_id')->nullable(); // ID Transaksi resmi dari Midtrans
    $table->string('snap_token')->nullable(); // Token Snap dari Midtrans
    $table->timestamp('paid_at')->nullable();
    $table->timestamps();
});

```

**13. `create_shipments_table**`

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

**14. `create_reviews_table**`

```php
Schema::create('reviews', function (Blueprint $table) {
    $table->id();
    $table->foreignId('product_id')->constrained()->onDelete('cascade');
    $table->foreignId('user_id')->constrained()->onDelete('cascade');
    $table->tinyInteger('rating'); // Skala 1 - 5
    $table->text('comment')->nullable();
    $table->timestamps();
});

```

**15. `create_wishlists_table**`

```php
Schema::create('wishlists', function (Blueprint $table) {
    $table->id();
    $table->foreignId('user_id')->constrained()->onDelete('cascade');
    $table->foreignId('product_id')->constrained()->onDelete('cascade');
    $table->timestamps();
    $table->unique(['user_id', 'product_id']); // Mencegah data duplikat
});

```

### 5.5 Eksekusi Migrasi

Jalankan skrip migrasi ke database:

```bash
php artisan migrate

```

*Jika ingin mengulang skema dari awal (perhatian: tindakan ini akan menghapus seluruh data yang ada):*

```bash
php artisan migrate:fresh

```

---

## 6. Konfigurasi Google OAuth

### 6.1 Membuat Kredensial di Google Cloud Console

1. Akses portal [Google Cloud Console](https://console.cloud.google.com/).
2. Buat proyek baru atau pilih proyek yang sudah tersedia.
3. Buka menu **APIs & Services → OAuth consent screen**, lengkapi informasi aplikasi, lalu pilih jenis **External**.
4. Navigasi ke **APIs & Services → Credentials → Create Credentials → OAuth Client ID**.
5. Pilih jenis aplikasi: **Web application**.
6. Tambahkan URI pada bagian **Authorized redirect URIs**:
```text
http://localhost:8000/api/auth/google/callback

```


7. Klik **Create**, simpan nilai **Client ID** dan **Client Secret** ke dalam berkas `.env` aplikasi Anda.

### 6.2 Mengonfigurasi `config/services.php`

Tambahkan array konfigurasi `google` berikut pada berkas `config/services.php`:

```php
'google' => [
    'client_id' => env('GOOGLE_CLIENT_ID'),
    'client_secret' => env('GOOGLE_CLIENT_SECRET'),
    'redirect' => env('GOOGLE_REDIRECT_URI'),
],

```

### 6.3 Membuat Controller Autentikasi

Jalankan perintah berikut untuk membuat *controller* penanganan OAuth:

```bash
php artisan make:controller Api/Auth/GoogleAuthController

```

Isi berkas `app/Http/Controllers/Api/Auth/GoogleAuthController.php`:

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
    /**
     * Mengarahkan pengguna ke halaman autentikasi Google.
     */
    public function redirect()
    {
        return Socialite::driver('google')
            ->stateless()
            ->redirect();
    }

    /**
     * Memproses callback data dari Google OAuth.
     */
    public function callback()
    {
        $googleUser = Socialite::driver('google')->stateless()->user();

        // Cari pengguna berdasarkan google_id atau alamat email
        $user = User::where('google_id', $googleUser->getId())
            ->orWhere('email', $googleUser->getEmail())
            ->first();

        if (!$user) {
            // Registrasi otomatis untuk pengguna baru
            $user = User::create([
                'name' => $googleUser->getName(),
                'email' => $googleUser->getEmail(),
                'google_id' => $googleUser->getId(),
                'avatar' => $googleUser->getAvatar(),
                'password' => bcrypt(Str::random(24)), // Password acak
                'role' => 'customer',
                'email_verified_at' => now(),
            ]);

            // Buat keranjang belanja kosong untuk pengguna baru
            Cart::create(['user_id' => $user->id]);
        } else if (!$user->google_id) {
            // Tautkan google_id jika akun email sudah ada sebelumnya
            $user->update([
                'google_id' => $googleUser->getId(),
                'avatar' => $googleUser->getAvatar(),
            ]);
        }

        // Buat token akses API Sanctum
        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'message' => 'Login berhasil',
            'user' => $user,
            'token' => $token,
        ]);
    }
}

```

### 6.4 Menambahkan Rute API

Buka berkas `routes/api.php` dan tambahkan rute berikut:

```php
use App\Http\Controllers\Api\Auth\GoogleAuthController;

// ==========================================
// AUTENTIKASI — GOOGLE OAUTH
// ==========================================
Route::get('/auth/google/redirect', [GoogleAuthController::class, 'redirect']);
Route::get('/auth/google/callback', [GoogleAuthController::class, 'callback']);

// Rute terproteksi Sanctum
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

```text
Pengguna menekan tombol "Login with Google" pada Frontend
        │
        ▼
Frontend mengarahkan ke: GET /api/auth/google/redirect
        │
        ▼
Laravel mengarahkan pengguna ke halaman persetujuan Google
        │
        ▼
Pengguna memilih akun Google dan memberikan izin akses
        │
        ▼
Google mengarahkan balik ke: GET /api/auth/google/callback
        │
        ▼
Laravel mengecek status pengguna:
  ├─ Pengguna Baru  ➜ Registrasi akun otomatis & buat keranjang belanja
  └─ Pengguna Lama ➜ Login & perbarui data profil
        │
        ▼
Laravel menerbitkan Personal Access Token (Sanctum)
        │
        ▼
API mengembalikan respons JSON berisi data { user, token } ke Frontend
        │
        ▼
Frontend menyimpan token untuk autentikasi API selanjutnya
(Menggunakan Header: Authorization: Bearer <token>)

```

---

## 8. Konfigurasi Midtrans Payment Gateway

### 8.1 Pendaftaran Akun Sandbox Midtrans

1. Daftar akun pada [Midtrans Sandbox Dashboard](https://dashboard.sandbox.midtrans.com/register).
2. Setelah masuk, buka menu **Settings → Access Keys**.
3. Salin informasi **Merchant ID**, **Client Key**, dan **Server Key**.
4. Masukkan seluruh kunci kredensial ke berkas `.env`.

### 8.2 Membuat Berkas Konfigurasi Midtrans

Buat berkas baru `config/midtrans.php`:

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

### 8.3 Membuat Layanan Service Midtrans

Buat direktori dan berkas `app/Services/MidtransService.php`:

```bash
mkdir -p app/Services

```

Isi berkas `app/Services/MidtransService.php`:

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

    /**
     * Membuat transaksi dan memperoleh Snap Token.
     */
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

    /**
     * Memproses objek notifikasi dari Webhook Midtrans.
     */
    public function handleNotification()
    {
        return new Notification();
    }
}

```

### 8.4 Membuat Controller Pembayaran

Jalankan perintah berikut:

```bash
php artisan make:controller Api/PaymentController

```

Isi berkas `app/Http/Controllers/Api/PaymentController.php`:

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

    /**
     * Membuat Snap Token untuk pesanan tertentu.
     */
    public function createSnapToken(Request $request, $orderId)
    {
        $order = Order::with(['user', 'address', 'orderItems.productVariant.product'])
            ->findOrFail($orderId);

        $snapToken = $this->midtransService->createTransaction($order);

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

    /**
     * Endpoint Webhook yang dipanggil otomatis oleh Midtrans.
     */
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
        $payment->paid_at = ($payment->status == 'settlement') ? now() : null;

        $payment->save();
        $order->save();

        return response()->json(['message' => 'Notifikasi berhasil diproses']);
    }
}

```

### 8.5 Menambahkan Rute Pembayaran

Buka berkas `routes/api.php` dan tambahkan rute berikut:

```php
use App\Http\Controllers\Api\PaymentController;

Route::middleware('auth:sanctum')->group(function () {
    Route::post('/orders/{orderId}/pay', [PaymentController::class, 'createSnapToken']);
});

// Rute Webhook (Bebas dari proteksi autentikasi user)
Route::post('/payment/notification', [PaymentController::class, 'notification']);

```

---

## 9. Konfigurasi ngrok untuk Webhook Midtrans

ngrok digunakan untuk meneruskan Notifikasi Pembayaran (*webhook*) dari *server* publik Midtrans ke *environment* lokal Anda.

### 9.1 Instalasi dan Autentikasi ngrok

Unduh aplikasi dari [ngrok Download](https://ngrok.com/download), lalu daftarkan authtoken akun Anda:

```bash
ngrok config add-authtoken <TOKEN_DARI_DASHBOARD_NGROK>

```

### 9.2 Menjalankan Laravel Server

```bash
php artisan serve

```

Aplikasi secara standar akan berjalan di `http://localhost:8000`.

### 9.3 Menjalankan ngrok Tunnel

Buka jendela terminal baru, lalu jalankan:

```bash
ngrok http 8000

```

Terminal akan menampilkan URL publik seperti contoh berikut:

```text
Forwarding    https://a1b2c3d4e5f6.ngrok-free.app -> http://localhost:8000

```

### 9.4 Mengonfigurasi Notification URL di Midtrans

1. Masuk ke [Dashboard Sandbox Midtrans](https://dashboard.sandbox.midtrans.com/).
2. Pilih menu **Settings → Configuration**.
3. Pada kolom **Payment Notification URL**, isi dengan URL ngrok Anda:
```text
https://a1b2c3d4e5f6.ngrok-free.app/api/payment/notification

```


4. Simpan perubahan.

### 9.5 Memperbarui Berkas `.env`

```env
MIDTRANS_NOTIFICATION_URL=https://a1b2c3d4e5f6.ngrok-free.app/api/payment/notification

```

> ⚠️ **Catatan Tambahan:** URL pada ngrok versi gratis akan berubah setiap kali sesi dijalankan ulang. Pastikan untuk selalu memperbarui URL di Dashboard Midtrans dan berkas `.env` saat memulai sesi pengembangan baru.

---

## 10. Alur Transaksi & Pembayaran

```text
Pengguna melakukan checkout dari keranjang belanja ➜ Sistem membuat data "orders" (Status: pending)
        │
        ▼
Frontend mengirimkan permintaan: POST /api/orders/{orderId}/pay
        │
        ▼
Backend meminta Snap Token ke API Midtrans
        │
        ▼
Backend menyimpan catatan "payments" (Status: pending, snap_token: xxx)
        │
        ▼
Frontend menampilkan antarmuka popup Midtrans Snap (Menggunakan snap_token & client_key)
        │
        ▼
Pengguna menyelesaikan pembayaran (Transfer Bank, E-Wallet, Kartu Kredit, dll.)
        │
        ▼
Midtrans mengirimkan Notifikasi HTTP ke Webhook: POST https://xxx.ngrok-free.app/api/payment/notification
        │
        ▼
Backend memproses notifikasi:
  ├─ Memperbarui "payments".status ➜ settlement / expire / cancel
  └─ Memperbarui "orders".status   ➜ paid / cancelled
        │
        ▼
Frontend melakukan pengecekan ulang status pesanan untuk memperbarui tampilan pengguna

```

---

## 11. Petunjuk Menjalankan Aplikasi

Buka dua jendela terminal untuk kebutuhan pengembangan:

**Terminal 1 — Server Laravel:**

```bash
php artisan serve

```

**Terminal 2 — ngrok (Dibutuhkan saat pengujian pembayaran):**

```bash
ngrok http 8000

```

Pastikan migrasi database telah dilakukan:

```bash
php artisan migrate

```

---

## 12. Struktur Direktori Penting

```text
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
├── services.php     (Kredensial OAuth Google)
└── midtrans.php     (Kredensial Midtrans)

database/
└── migrations/
    └── (15 berkas migrasi tabel)

routes/
└── api.php

```

---

## 13. Panduan Pengujian API

**1. Login / Registrasi via Google OAuth:**
Buka tautan berikut langsung pada *browser* (karena membutuhkan alur pengalihan halaman):

```text
http://localhost:8000/api/auth/google/redirect

```

**2. Memeriksa Data Pengguna Terautentikasi:**
Gunakan aplikasi penguji API seperti Postman atau Insomnia. Tambahkan *header*: `Authorization: Bearer <token>`

```text
GET http://localhost:8000/api/user

```

**3. Membuat Snap Token Pembayaran:**

```text
POST http://localhost:8000/api/orders/1/pay
Authorization: Bearer <token>

```

**4. Pengujian Simulasi Webhook Midtrans:**
Dipanggil secara otomatis oleh *server* Midtrans setelah transaksi pembayaran pada lingkungan *sandbox* selesai dilakukan. Dapat juga diuji secara manual melalui Postman ke URL ngrok:

```text
POST https://xxxx.ngrok-free.app/api/payment/notification

```

---

## 14. Penanganan Masalah (Troubleshooting)

| Masalah | Penyebab | Solusi |
| --- | --- | --- |
| `SQLSTATE[HY000] [1045] Access denied` | Kredensial MySQL tidak sesuai. | Periksa kembali nilai `DB_USERNAME` dan `DB_PASSWORD` pada berkas `.env`. |
| Error Google Redirect `redirect_uri_mismatch` | URI pengalihan tidak cocok. | Pastikan URL yang terdaftar di Google Cloud Console **persis sama** dengan nilai `GOOGLE_REDIRECT_URI` di `.env`. |
| Webhook Midtrans tidak terikat ke Laravel | ngrok mati atau URL tidak diperbarui. | Pastikan sesi ngrok aktif dan URL *Configuration* pada Dashboard Midtrans telah disesuaikan dengan URL ngrok terbaru. |
| `Class "Midtrans\Config" not found` | Dependensi SDK belum terinstal sempurna. | Jalankan perintah `composer require midtrans/midtrans-php` diikuti dengan `composer dump-autoload`. |
| Token Sanctum ditolak / Unauthenticated | Header permintaan kurang atau salah. | Pastikan *header* `Authorization: Bearer <token>` terikutsertakan dan rute tujuan berada di dalam grup `auth:sanctum`. |
| Kendala CORS pada aplikasi Frontend | Akses antar-domain diblokir. | Konfigurasikan pengaturan CORS pada berkas `config/cors.php` bawaan Laravel. |

---

## Ringkasan Berkas Environment Variable (`.env`)

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