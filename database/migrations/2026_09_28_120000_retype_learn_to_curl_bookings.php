<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

// Learn to Curl was saved as a private booking, which the public ice calendar hides.
return new class extends Migration
{
    public function up(): void
    {
        DB::table('bookings')
            ->where('event_type', 'private')
            ->whereRaw("lower(title) like '%learn%curl%'")
            ->update(['event_type' => 'learn_to_curl']);
    }

    public function down(): void
    {
        DB::table('bookings')
            ->where('event_type', 'learn_to_curl')
            ->update(['event_type' => 'private']);
    }
};
