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
        Schema::create('board_agendas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('board_meeting_id')->constrained()->cascadeOnDelete();
            $table->string('title');
            $table->text('description')->nullable();
            $table->unsignedInteger('sort_order')->default(0);
            $table->unsignedInteger('duration_minutes')->nullable();
            $table->foreignId('presenter_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index(['board_meeting_id', 'sort_order']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('board_agendas');
    }
};
