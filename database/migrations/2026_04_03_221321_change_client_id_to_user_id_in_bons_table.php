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
        Schema::table('bons', function (Blueprint $table) {
            // Drop old foreign key if exists
            $table->dropForeign(['client_id']);

            // Rename column
            $table->renameColumn('client_id', 'user_id');
        });

        Schema::table('bons', function (Blueprint $table) {
            // Add new foreign key to users table
            $table->foreign('user_id')
                  ->references('id')
                  ->on('users')
                  ->cascadeOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('bons', function (Blueprint $table) {
            // Drop new foreign key
            $table->dropForeign(['user_id']);

            // Rename column back to client_id
            $table->renameColumn('user_id', 'client_id');
        });

        Schema::table('bons', function (Blueprint $table) {
            // Restore old foreign key to clients table
            $table->foreign('client_id')
                  ->references('id')
                  ->on('clients')
                  ->cascadeOnDelete();
        });
    }
};