<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    // DRIVER becomes a user <-> vehicle link table (same shape as pengawas_vehicle),
    // so one pemandu (USERS row, module PENYELENGGARAAN role 65) can handle many vehicles.
    public function up(): void
    {
        if (Schema::hasTable('driver')) {
            if (Schema::hasColumn('driver', 'user_id')) {
                return;
            }

            if (DB::table('driver')->count() > 0) {
                throw new RuntimeException('Table DRIVER is not empty; refusing to recreate it.');
            }

            Schema::drop('driver');
        }

        Schema::create('driver', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->unsignedBigInteger('vehicle_id');
            $table->string('status_data', 20)->default('ACTIVE');
            $table->string('created_by', 100)->nullable();
            $table->timestamp('created_at', 6)->useCurrent()->nullable();
            $table->string('updated_by', 100)->nullable();
            $table->timestamp('updated_at', 6)->nullable();
            $table->index(['user_id', 'status_data']);
            $table->index(['vehicle_id', 'status_data']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('driver');
    }
};
