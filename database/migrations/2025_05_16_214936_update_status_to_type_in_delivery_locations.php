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
        Schema::table('delivery_locations', function (Blueprint $table) {
            if (Schema::hasColumn('delivery_locations', 'status')) {
                $table->dropColumn('status');
            }
    
            $table->enum('type', ['pickup', 'dropoff']);
            $table->string('address');
        });
    }
    
    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('delivery_locations', function (Blueprint $table) {
            $table->dropColumn('type');
            $table->dropColumn('address');
            $table->enum('status', ['en_route', 'nearby', 'delivered']);
        });
    }
};
