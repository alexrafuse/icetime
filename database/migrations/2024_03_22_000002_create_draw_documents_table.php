<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('draw_documents', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->unsignedTinyInteger('day_of_week'); // 2=Tuesday, 3=Wednesday, 4=Thursday, 5=Friday
            $table->string('file_path');
            $table->date('valid_from');
            $table->date('valid_until')->nullable();
            $table->timestamps();

            $table->index(['day_of_week', 'valid_from', 'valid_until']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('draw_documents');
    }
}; 