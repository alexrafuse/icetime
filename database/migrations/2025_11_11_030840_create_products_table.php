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
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->foreignId('season_id')->constrained()->cascadeOnDelete();
            $table->unsignedBigInteger('curlingio_id')->nullable()->unique();
            $table->string('name');
            $table->string('slug');
            $table->text('description')->nullable();
            $table->string('product_type');
            $table->string('membership_tier')->nullable();
            $table->integer('price_cents');
            $table->string('currency', 3)->default('CAD');
            $table->boolean('is_available')->default(true);
            $table->json('metadata')->nullable();
            $table->timestamps();

            $table->index(['season_id', 'product_type']);
            $table->index(['season_id', 'is_available']);
            $table->index(['season_id', 'curlingio_id']); // For curlingio_id matching
            $table->index(['season_id', 'price_cents']); // For price-based matching
            $table->unique(['season_id', 'slug']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};
