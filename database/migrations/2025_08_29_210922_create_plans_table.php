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
        Schema::create('plans', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('stripe_product_id');
            $table->string('stripe_price_id');
            $table->enum('type', ['free', 'premium', 'professional']);
            $table->decimal('price', 8, 2);
            $table->string('currency')->default('eur');
            $table->string('interval')->default('month'); // month, year
            $table->integer('query_limit')->nullable(); // null = ilimitado
            $table->text('features')->nullable(); // JSON con características
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('plans');
    }
};
