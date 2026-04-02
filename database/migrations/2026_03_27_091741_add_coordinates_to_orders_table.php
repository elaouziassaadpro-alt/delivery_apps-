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
            // 10 total digits, 8 after the decimal point (High precision for GPS)
            // We place them after the 'location' column for better organization
            $table->decimal('lat', 10, 8)->nullable()->after('location');
            $table->decimal('lng', 11, 8)->nullable()->after('lat');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn(['lat', 'lng']);
        });
    }
};