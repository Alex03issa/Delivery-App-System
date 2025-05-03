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
        Schema::create('deliveries', function (Blueprint $table) {
            $table->id(); // Auto-increment primary key
            $table->foreignId('driver_id')->constrained()->onDelete('cascade'); // Assuming there's a Driver model
            $table->foreignId('client_id')->constrained()->onDelete('cascade'); // Assuming there's a Client model
            $table->string('package_details'); // Example field for package details
            $table->string('delivery_status'); // Example field for delivery status
            $table->date('delivery_date'); // Example field for the date of delivery
            $table->decimal('total_price', 10, 2); // Example field for total price
            $table->timestamps(); // created_at and updated_at fields
        });
    }
    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('deliveries');
    }
};
