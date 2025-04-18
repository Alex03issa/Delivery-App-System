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
        Schema::create('drivers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('vehicle_type');
            $table->string('vehicle_brand')->nullable();
            $table->string('vehicle_model')->nullable();
            $table->string('vehicle_color')->nullable();
            $table->string('vehicle_year')->nullable();
            $table->string('plate_number');
            $table->string('license_number');
            $table->date('license_expiry')->nullable();
            $table->string('registration_document')->nullable();
            $table->enum('pricing_type', ['fixed', 'per_km']);
            $table->decimal('price_per_km', 8, 2)->nullable();
            $table->decimal('fixed_price', 8, 2)->nullable();
            $table->boolean('is_approved')->default(false);
            $table->json('availability')->nullable();
            $table->decimal('earnings', 10, 2)->default(0);
            $table->integer('delivery_count')->default(0);
            $table->decimal('km_completed', 8, 2)->default(0);
            $table->float('rating_average')->default(5.0);
            $table->integer('warning_count')->default(0);
            $table->enum('visibility_status', ['visible', 'hidden'])->default('visible');
            $table->timestamps();
        });
        
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('drivers');
    }
};
