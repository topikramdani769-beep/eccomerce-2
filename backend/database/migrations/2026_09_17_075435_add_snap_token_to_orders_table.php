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
    Schema::table('orders', function (Blueprint $table) {
        if (!Schema::hasColumn('orders', 'order_number')) {
            $table->string('order_number')->unique()->nullable();
        }
        if (!Schema::hasColumn('orders', 'snap_token')) {
            // Hapus ->after('total_price') agar tidak error
            $table->string('snap_token')->nullable(); 
        }
    });
}

public function down(): void
{
    Schema::table('orders', function (Blueprint $table) {
        $table->dropColumn(['order_number', 'snap_token']);
    });
}
};
