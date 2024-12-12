<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('availabilities', function (Blueprint $table) {
            $table->id();
            $table->foreignId('area_id')->constrained()->cascadeOnDelete();
            $table->integer('day_of_week')->nullable(); // 0 = Sunday, 6 = Saturday, null for specific dates
            $table->dateTime('start_time');
            $table->dateTime('end_time');
            $table->boolean('is_available')->default(true);
            $table->string('note')->nullable();
            $table->timestamps();

            // $table->unique(['area_id', 'day_of_week'], 'unique_weekly_availability');
            // DB::statement('CREATE UNIQUE INDEX unique_specific_date_availability ON availabilities (area_id, date(start_time)) WHERE day_of_week IS NULL');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('availabilities');
    }
}; 