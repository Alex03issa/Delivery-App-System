<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('locations', function (Blueprint $table) {
            $table->dropColumn('type');
        });

        Schema::table('locations', function (Blueprint $table) {
            $table->foreignId('delivery_request_id')->nullable()->constrained()->onDelete('cascade');
            $table->enum('type', ['pickup', 'dropoff']);
        });
    }

    public function down(): void
    {
        Schema::table('locations', function (Blueprint $table) {
            $table->dropForeign(['delivery_request_id']);
            $table->dropColumn('delivery_request_id');
            $table->dropColumn('type');
            
            $table->enum('type', ['home', 'office', 'custom'])->change(); // rollback to original
        });
    }

};
