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
        Schema::create('reviews', function (Blueprint $table) {
            $table->id();
            $table->foreignId('delivery_id')->constrained('delivery_requests')->onDelete('cascade');
            $table->foreignId('reviewer_id')->constrained('users')->onDelete('cascade'); // Who gives the review
            $table->foreignId('reviewed_id')->constrained('users')->onDelete('cascade'); // Who is being reviewed
            $table->enum('reviewer_role', ['client', 'driver']);
            $table->enum('reviewed_role', ['client', 'driver']);
            $table->tinyInteger('rating'); // 1–5
            $table->text('comment')->nullable();
            $table->boolean('is_flagged')->default(false);
        
            $table->timestamps();
        });
        
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('reviews');
    }
};
