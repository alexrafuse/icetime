<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('resources', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->text('description')->nullable();
            $table->string('category');
            $table->string('type'); // 'url' or 'file'
            $table->string('url')->nullable();
            $table->string('file_path')->nullable();
            $table->string('visibility')->default('all'); // 'all' or 'admin_staff_only'
            $table->integer('priority')->default(999);
            $table->boolean('is_active')->default(true);
            $table->date('valid_from')->nullable();
            $table->date('valid_until')->nullable();
            $table->timestamps();

            $table->index(['category', 'is_active', 'priority']);
            $table->index('visibility');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('resources');
    }
};
