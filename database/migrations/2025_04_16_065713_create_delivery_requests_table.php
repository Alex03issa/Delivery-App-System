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
        Schema::create('delivery_requests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('client_id')->constrained('clients')->onDelete('cascade');
            $table->foreignId('driver_id')->nullable()->constrained('drivers')->nullOnDelete();
            $table->text('pickup_location');
            $table->text('dropoff_location');
            $table->decimal('length_cm', 8, 2);
            $table->decimal('width_cm', 8, 2);
            $table->decimal('height_cm', 8, 2);
            $table->decimal('package_volume', 10, 2)->nullable();
            $table->decimal('package_weight', 8, 2);
            $table->enum('package_size', ['small', 'medium', 'large'])->nullable();
            $table->decimal('distance_km', 8, 2)->nullable();
            $table->decimal('extra_charge', 8, 2)->default(0);
            $table->decimal('price', 10, 2);
            $table->enum('urgency_level', ['normal', 'urgent']);
            $table->enum('status', ['pending', 'accepted', 'in_progress', 'delivered', 'canceled'])->default('pending');
            $table->dateTime('delivery_date');
            $table->timestamp('assigned_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->enum('payment_method', ['card', 'crypto', 'cod']);
            $table->boolean('is_paid')->default(false);
            $table->text('note')->nullable();
            $table->string('contact_phone')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('delivery_requests');
    }
};
