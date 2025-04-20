<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('delivery_requests', function (Blueprint $table) {

            $table->dropColumn('contact_phone');
            $table->string('tracking_code')->nullable()->unique();
            $table->string('pickup_contact_name')->nullable();
            $table->string('pickup_contact_phone')->nullable();
            $table->string('dropoff_contact_name')->nullable();
            $table->string('dropoff_contact_phone')->nullable();
            $table->timestamp('scheduled_pickup_at')->nullable();
            $table->text('cancellation_reason')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('delivery_requests', function (Blueprint $table) {

            $table->dropColumn([
                'tracking_code',
                'pickup_contact_name',
                'pickup_contact_phone',
                'dropoff_contact_name',
                'dropoff_contact_phone',
                'scheduled_pickup_at',
                'cancellation_reason',
            ]);

            $table->string('contact_phone')->nullable();
        });
    }
};
