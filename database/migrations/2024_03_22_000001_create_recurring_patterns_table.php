<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('recurring_patterns', function (Blueprint $table) {
            $table->id();
            $table->foreignId('booking_id')->constrained()->cascadeOnDelete();
            $table->string('frequency'); // DAILY, WEEKLY, MONTHLY
            $table->integer('interval')->default(1); // Every X days/weeks/months
            $table->date('start_date');
            $table->date('end_date');
            $table->json('days_of_week')->nullable(); // For weekly patterns
            $table->json('excluded_dates')->nullable(); // Dates to skip
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('recurring_patterns');
    }
};