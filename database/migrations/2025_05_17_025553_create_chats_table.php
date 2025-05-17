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
        Schema::create('chats', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('send_by');
            $table->unsignedBigInteger('send_to');
            $table->string('message_type')->default('text');
            $table->text('message');
            $table->timestamp('date_time');
            $table->boolean('is_received')->default(0);
            $table->timestamps();
        
            $table->foreign('send_by')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('send_to')->references('id')->on('users')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('chats');
    }
};
