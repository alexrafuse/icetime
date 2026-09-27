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
        Schema::create('club_bylaws', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->string('slug')->index();
            $table->string('article_number', 50);
            $table->longText('content')->nullable();
            $table->string('file_path')->nullable();
            $table->unsignedInteger('version')->default(1);
            $table->string('status', 50)->default('draft');
            $table->date('effective_date')->nullable();
            $table->foreignId('parent_id')->nullable()->constrained('club_bylaws')->nullOnDelete();
            $table->foreignId('approved_by_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->dateTime('published_at')->nullable();
            $table->timestamps();

            $table->index(['status', 'slug']);
            $table->index('article_number');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('club_bylaws');
    }
};
