<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    // A servis form with every kelulusan recorded is now COMPLETE (was APPROVED).
    public function up(): void
    {
        DB::table('servis_harian')->where('status', 'APPROVED')->update(['status' => 'COMPLETE']);
    }

    public function down(): void
    {
        DB::table('servis_harian')->where('status', 'COMPLETE')->update(['status' => 'APPROVED']);
    }
};
