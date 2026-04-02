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
        Schema::create('bons', function (Blueprint $table) {
            $table->id();
            $table->string('code')->unique();
            $table->foreignId('client_id')->constrained()->onDelete('cascade');
            $table->string('status')->default('pending');
            $table->string('payment_status')->default('pending');
            $table->string('payment_method')->default('cash');
            $table->string('delivery_type')->nullable();
            $table->string('pickup_date')->nullable();
            $table->decimal('price', 10, 2);
            $table->decimal('driver_commission', 10, 2)->nullable();
            $table->decimal('commission', 10, 2)->nullable();
            $table->decimal('weight', 10, 2)->nullable();
            $table->decimal('dimensions_length', 10, 2)->nullable();
            $table->decimal('dimensions_width', 10, 2)->nullable();
            $table->decimal('dimensions_height', 10, 2)->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('bons');
    }
};
