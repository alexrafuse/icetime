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
        Schema::create('donations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('donor_id')->constrained()->cascadeOnDelete();
            $table->string('type', 50);
            $table->unsignedInteger('amount_cents')->nullable();
            $table->text('description')->nullable();
            $table->date('donated_at');
            $table->foreignId('season_id')->nullable()->constrained()->nullOnDelete();
            $table->string('receipt_number', 100)->nullable();
            $table->boolean('is_tax_receipted')->default(false);
            $table->timestamps();

            $table->index(['donor_id', 'donated_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('donations');
    }
};
