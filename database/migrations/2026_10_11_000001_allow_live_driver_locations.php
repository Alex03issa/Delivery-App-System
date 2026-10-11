<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Live GPS tracking stores the driver's last position in `locations` with
 * type "live". The earlier migrations limited `type` to pickup/dropoff and
 * required an address, so every tracking write failed.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('locations', function (Blueprint $table) {
            $table->enum('type', ['pickup', 'dropoff', 'live'])->change();
            $table->text('address')->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('locations', function (Blueprint $table) {
            $table->enum('type', ['pickup', 'dropoff'])->change();
            $table->text('address')->nullable(false)->change();
        });
    }
};
