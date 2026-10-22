<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    // Job Controller -> Penolong Jurutera -> Pegawai Jurutera approval flow
    // (Kimpalan, Aircond, Pembaikan Berat/Ringan, Servis).
    private const COLUMNS = [
        'repair_mode' => ['string', 20],        // INTERNAL / BENGKEL_PANEL
        'bengkel_panel_id' => ['unsignedBigInteger', null],
        'jc_remark' => ['longText', null],
        'jc_by' => ['string', 100],
        'jc_at' => ['timestamp', null],
        'no_arahan_kerja' => ['string', 100],   // generated when PJ approves
        'pj_by' => ['string', 100],
        'pj_at' => ['timestamp', null],
        'jurutera_by' => ['string', 100],
        'jurutera_at' => ['timestamp', null],
        'remark_reject' => ['longText', null],
        'rejected_by' => ['string', 100],
        'rejected_at' => ['timestamp', null],
        'rejected_stage' => ['string', 20],     // PJ / JURUTERA
    ];

    public function up(): void
    {
        Schema::table('penyelenggaraan_vehicle', function (Blueprint $table) {
            foreach (self::COLUMNS as $name => [$type, $length]) {
                if (Schema::hasColumn('penyelenggaraan_vehicle', $name)) {
                    continue;
                }

                match ($type) {
                    'string' => $table->string($name, $length)->nullable(),
                    'timestamp' => $table->timestamp($name, 6)->nullable(),
                    default => $table->{$type}($name)->nullable(),
                };
            }
        });
    }

    public function down(): void
    {
        Schema::table('penyelenggaraan_vehicle', function (Blueprint $table) {
            foreach (array_keys(self::COLUMNS) as $name) {
                if (Schema::hasColumn('penyelenggaraan_vehicle', $name)) {
                    $table->dropColumn($name);
                }
            }
        });
    }
};
