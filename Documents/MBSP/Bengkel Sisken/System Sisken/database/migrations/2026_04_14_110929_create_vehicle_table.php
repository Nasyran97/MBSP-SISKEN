<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('vehicle', function (Blueprint $table) {
            $table->id();
            $table->string('no_plat', 50);
            $table->string('jabatan', 255)->nullable();
            $table->string('no_chasis', 150)->nullable();
            $table->string('no_enjin', 150)->nullable();
            $table->string('buatan', 150)->nullable();
            $table->string('model', 150)->nullable();
            $table->string('jenis_badan', 100)->nullable();
            $table->string('jenis_kenderaan', 150)->nullable();
            $table->string('tahun_dibuat', 10)->nullable();
            $table->dateTime('tarikh_pendaftaran')->nullable();
            $table->string('kelas_kenderaan', 150)->nullable();
            $table->string('keupayaan_enjin', 100)->nullable();
            $table->string('kelas_kegunaan', 150)->nullable();
            $table->string('jenis_bahan_bakar', 100)->nullable();
            $table->string('lokasi_penempatan', 255)->nullable();
            $table->timestamp('created_at', 6)->useCurrent()->nullable();
            $table->string('created_by', 100)->nullable();
            $table->timestamp('updated_at', 6)->nullable();
            $table->string('updated_by', 100)->nullable();
            $table->string('status_data', 20)->default('ACTIVE');
            $table->string('geran_file', 500)->nullable();
            $table->string('cn_file', 500)->nullable();
            $table->string('kod_jabatan', 50)->nullable();
            $table->dateTime('tarikh_beli')->nullable();
            $table->decimal('harga_beli', 14, 2)->nullable();
            $table->unsignedBigInteger('pembekal_id')->nullable();
        });

        Schema::create('driver', function (Blueprint $table) {
            $table->unsignedBigInteger('id');
            $table->unsignedBigInteger('vehicle_id')->unique();
            $table->string('staff_no', 50)->unique();
            $table->string('driver_name', 255);
            $table->string('position_name', 255)->nullable();
            $table->string('position_code', 50)->nullable();
            $table->string('department_name', 255)->nullable();
            $table->string('department_code', 50)->nullable();
            $table->string('email', 255)->nullable();
            $table->string('phone_no', 50)->nullable();
            $table->string('license_no', 100)->nullable();
            $table->dateTime('license_expiry_date')->nullable();
            $table->string('created_by', 50)->nullable();
            $table->timestamp('created_at', 6)->nullable();
            $table->string('edited_by', 50)->nullable();
            $table->timestamp('edited_at', 6)->nullable();
            $table->string('driver_status', 50)->default('ACTIVE');
            $table->string('status_data', 20)->default('ACTIVE');
            $table->primary('id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('driver');
        Schema::dropIfExists('vehicle');
    }
};
