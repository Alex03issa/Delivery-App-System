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
        Schema::create('package_size_rules', function (Blueprint $table) {
            $table->id();
            $table->string('label'); // e.g., "Small"
            $table->decimal('max_volume', 10, 2);
            $table->decimal('max_weight', 8, 2);
            $table->enum('size_category', ['small', 'medium', 'large']);
            $table->boolean('active')->default(true);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('package_size_rules');
    }
};
