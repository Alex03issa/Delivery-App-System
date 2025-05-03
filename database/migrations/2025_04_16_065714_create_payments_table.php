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
            $table->foreignId('delivery_id')->constrained('delivery_requests')->onDelete('cascade');
            $table->enum('payment_method', ['card', 'crypto', 'cod']);
            $table->foreignId('currency_id')->constrained('currencies')->onDelete('restrict');
            $table->decimal('amount', 10, 2);
            $table->enum('payment_status', ['pending', 'confirmed', 'failed'])->default('pending');
            $table->string('transaction_id')->nullable();
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
