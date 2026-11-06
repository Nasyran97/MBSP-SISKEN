<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasColumn('vehicle_kemalangan', 'tuntutan_pihak_ketiga_id')) {
            Schema::table('vehicle_kemalangan', function (Blueprint $table) {
                $table->unsignedBigInteger('tuntutan_pihak_ketiga_id')->nullable();
            });
        }

        // Backfill existing rows by matching the stored name text to the lookup table.
        $lookup = DB::table('tuntutan_pihak_ketiga')->get();

        foreach ($lookup as $row) {
            DB::table('vehicle_kemalangan')
                ->whereNull('tuntutan_pihak_ketiga_id')
                ->whereRaw('UPPER(TRIM(jenis_tuntutan_pihak_ketiga)) = ?', [strtoupper(trim($row->tuntutan_pihak_ketiga_name))])
                ->update(['tuntutan_pihak_ketiga_id' => $row->id]);
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('vehicle_kemalangan', 'tuntutan_pihak_ketiga_id')) {
            Schema::table('vehicle_kemalangan', function (Blueprint $table) {
                $table->dropColumn('tuntutan_pihak_ketiga_id');
            });
        }
    }
};
