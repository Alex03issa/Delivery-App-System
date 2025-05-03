<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('driver_strikes', function (Blueprint $table) {
            $table->tinyInteger('severity_level')->default(1)->after('review_id');
            $table->boolean('is_resolved')->default(false)->after('severity_level');
            $table->foreignId('driver_log_id')->nullable()->constrained('driver_logs')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('driver_strikes', function (Blueprint $table) {
            $table->dropForeign(['driver_log_id']);
            $table->dropColumn(['severity_level', 'is_resolved', 'driver_log_id']);
        });
    }

};
