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
        Schema::create('user_products', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('product_id')->constrained()->cascadeOnDelete();
            $table->foreignId('season_id')->constrained()->cascadeOnDelete();
            $table->integer('price_paid_cents')->nullable();
            $table->timestamp('assigned_at');
            $table->timestamp('expires_at')->nullable();
            $table->string('status');
            $table->string('purchase_reference')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamps();

            $table->index(['user_id', 'season_id']);
            $table->index(['user_id', 'status']);
            $table->index(['product_id', 'status']);
            $table->index('assigned_at');
            $table->index(['user_id', 'product_id', 'season_id']); // For duplicate membership checks
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_products');
    }
};
