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
        Schema::table('user_products', function (Blueprint $table) {
            $table->integer('refund_amount_cents')->nullable()->after('price_paid_cents');
            $table->string('refund_reason')->nullable()->after('refund_amount_cents');
            $table->timestamp('refunded_at')->nullable()->after('refund_reason');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('user_products', function (Blueprint $table) {
            $table->dropColumn(['refund_amount_cents', 'refund_reason', 'refunded_at']);
        });
    }
};
