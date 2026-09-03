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
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payments');
    }
};
