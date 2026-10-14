<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('kalendar_maintenance')) {
            return;
        }

        Schema::create('kalendar_maintenance', function (Blueprint $table) {
            $table->id();
            $table->date('tarikh');
            $table->smallInteger('tahun');
            $table->string('sesi', 10); // PAGI / PETANG
            $table->unsignedBigInteger('vehicle_id');
            $table->string('status_data', 20)->default('ACTIVE');
            $table->string('created_by', 100)->nullable();
            $table->timestamp('created_at', 6)->useCurrent()->nullable();
            $table->string('updated_by', 100)->nullable();
            $table->timestamp('updated_at', 6)->nullable();
            $table->index(['tahun', 'status_data']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('kalendar_maintenance');
    }
};
