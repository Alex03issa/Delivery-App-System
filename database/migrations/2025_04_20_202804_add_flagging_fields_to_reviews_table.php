<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('reviews', function (Blueprint $table) {
            $table->text('flag_reason')->nullable()->after('is_flagged');
            $table->enum('flagged_by_type', ['admin', 'system'])->nullable();
            $table->foreignId('flagged_by_id')->nullable()->constrained('users')->nullOnDelete();
            $table->tinyInteger('severity_level')->nullable()->after('flagged_by_id');
        });
    }
    
    public function down(): void
    {
        Schema::table('reviews', function (Blueprint $table) {
            $table->dropColumn(['flag_reason','flagged_by_type', 'flagged_by_id', 'severity_level']);
        });
    }
    
};
