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
        Schema::table('payments', function (Blueprint $table) {
            $table->decimal('platform_fee', 10, 2)->default(0)->after('amount');
            $table->decimal('driver_share', 10, 2)->default(0)->after('platform_fee');
            $table->decimal('center_share', 10, 2)->default(0)->after('driver_share');
            $table->decimal('converted_amount', 10, 2)->nullable()->after('center_share');
            $table->decimal('conversion_rate', 10, 4)->nullable()->after('converted_amount');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('payments', function (Blueprint $table) {
            $table->dropColumn([
                'platform_fee',
                'driver_share',
                'center_share',
                'converted_amount',
                'conversion_rate',
                'currency_id'
            ]);
        });
    }
};
