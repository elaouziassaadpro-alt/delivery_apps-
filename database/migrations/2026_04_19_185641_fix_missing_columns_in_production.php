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
        // Fix missing 'bon_id' in orders table
        if (Schema::hasTable('orders') && !Schema::hasColumn('orders', 'bon_id')) {
            Schema::table('orders', function (Blueprint $table) {
                $table->foreignId('bon_id')->nullable()->constrained('bons')->onDelete('cascade');
            });
        }

        // Fix missing 'bon_driver_id' in orders table
        if (Schema::hasTable('orders') && !Schema::hasColumn('orders', 'bon_driver_id')) {
            Schema::table('orders', function (Blueprint $table) {
                $table->foreignId('bon_driver_id')->nullable()->constrained('bons')->onDelete('cascade');
            });
        }

        // Fix missing 'is_completed' in bons table
        if (Schema::hasTable('bons') && !Schema::hasColumn('bons', 'is_completed')) {
            Schema::table('bons', function (Blueprint $table) {
                $table->boolean('is_completed')->default(false);
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // No down migration needed for a fix
    }
};
