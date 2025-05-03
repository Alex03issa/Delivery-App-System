<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::create('earnings', function (Blueprint $table) {
            $table->id(); // Auto-increment primary key
            $table->foreignId('driver_id')->constrained('drivers')->onDelete('cascade'); // Foreign key to drivers
            $table->foreignId('delivery_id')->constrained('deliveries')->onDelete('cascade'); // Foreign key to deliveries
            $table->decimal('total_revenue', 10, 2);
            $table->decimal('commission', 10, 2);
            $table->decimal('pending_payment', 10, 2);
            $table->timestamps(); // created_at and updated_at fields
        });
    }
    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('earnings');
    }
};
