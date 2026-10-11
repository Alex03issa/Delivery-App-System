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
        Schema::table('payments', function (Blueprint $table) {
            $table->decimal('amount', 18, 10)->change();
            $table->decimal('converted_amount', 18, 10)->nullable()->change();
            $table->decimal('conversion_rate', 18, 10)->nullable()->change();
        });
    }
    
    public function down()
    {
        Schema::table('payments', function (Blueprint $table) {
            $table->decimal('amount', 10, 2)->change();
            $table->decimal('converted_amount', 10, 2)->nullable()->change();
            $table->decimal('conversion_rate', 10, 4)->nullable()->change();
        });
    }
};
