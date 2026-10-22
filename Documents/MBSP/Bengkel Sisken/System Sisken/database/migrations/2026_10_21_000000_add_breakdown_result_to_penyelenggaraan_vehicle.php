<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('penyelenggaraan_vehicle', function (Blueprint $table) {
            if (!Schema::hasColumn('penyelenggaraan_vehicle', 'breakdown_result')) {
                $table->string('breakdown_result', 30)->nullable();   // PEMBAIKAN_TERUS / PUSKEB / BENGKEL_PANEL
            }

            if (!Schema::hasColumn('penyelenggaraan_vehicle', 'towing')) {
                $table->string('towing', 1)->nullable();              // Y / N (PUSKEB & BENGKEL_PANEL only)
            }
        });
    }

    public function down(): void
    {
        Schema::table('penyelenggaraan_vehicle', function (Blueprint $table) {
            foreach (['breakdown_result', 'towing'] as $column) {
                if (Schema::hasColumn('penyelenggaraan_vehicle', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
