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
            $table->string('title');
            $table->string('frequency');
            $table->integer('interval')->default(1);
            $table->date('start_date');
            $table->date('end_date');
            $table->json('days_of_week')->nullable();
            $table->json('excluded_dates')->nullable();
            $table->foreignId('primary_booking_id')->nullable()->constrained('bookings')->nullOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('recurring_patterns');
    }
};
