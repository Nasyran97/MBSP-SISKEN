<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasColumn('users', 'no_telefon')) {
            Schema::table('users', function (Blueprint $table) {
                $table->string('no_telefon', 50)->nullable();
            });
        }

        // One servis form per KALENDAR_SERVICE entry (a car scheduled on a day).
        if (!Schema::hasTable('servis_harian')) {
            Schema::create('servis_harian', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('kalendar_service_id')->unique();
                $table->unsignedBigInteger('vehicle_id');
                $table->date('tarikh');
                $table->unsignedBigInteger('driver_user_id')->nullable();
                $table->string('no_telefon', 50)->nullable();
                $table->string('depo', 255)->nullable();
                $table->integer('odometer')->nullable();
                $table->string('masa_masuk', 5)->nullable();
                $table->string('masa_keluar', 5)->nullable();
                $table->string('no_arahan_kerja', 100)->nullable()->unique();
                $table->string('status', 20)->default('DRAFT'); // DRAFT / SUBMITTED / COMPLETE
                $table->unsignedBigInteger('submitted_by')->nullable();
                $table->timestamp('submitted_at', 6)->nullable();
                $table->unsignedBigInteger('jkjg_user_id')->nullable();
                $table->timestamp('jkjg_approved_at', 6)->nullable();
                $table->unsignedBigInteger('driver_approved_by')->nullable();
                $table->timestamp('driver_approved_at', 6)->nullable();
                $table->unsignedBigInteger('jurutera_user_id')->nullable();
                $table->timestamp('jurutera_approved_at', 6)->nullable();
                $table->string('status_data', 20)->default('ACTIVE');
                $table->string('created_by', 100)->nullable();
                $table->timestamp('created_at', 6)->useCurrent()->nullable();
                $table->string('updated_by', 100)->nullable();
                $table->timestamp('updated_at', 6)->nullable();
            });
        }

        if (!Schema::hasTable('servis_harian_item')) {
            Schema::create('servis_harian_item', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('servis_harian_id');
                $table->unsignedBigInteger('checklist_servis_id');
                $table->string('keputusan', 10)->nullable(); // OK / NOT OK
                $table->string('catatan', 500)->nullable();
                $table->string('tindakan', 500)->nullable();
                $table->timestamp('created_at', 6)->useCurrent()->nullable();
                $table->timestamp('updated_at', 6)->nullable();
                $table->unique(['servis_harian_id', 'checklist_servis_id'], 'uq_servis_item');
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('servis_harian_item');
        Schema::dropIfExists('servis_harian');

        if (Schema::hasColumn('users', 'no_telefon')) {
            Schema::table('users', function (Blueprint $table) {
                $table->dropColumn('no_telefon');
            });
        }
    }
};
