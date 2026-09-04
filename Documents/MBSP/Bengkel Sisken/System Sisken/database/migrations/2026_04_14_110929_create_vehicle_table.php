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
            $table->string('plate_no', 50)->unique();
            $table->string('asset_no', 100)->nullable();
            $table->string('vehicle_type', 100)->nullable();
            $table->string('brand', 100)->nullable();
            $table->string('model', 100)->nullable();
            $table->string('manufacture_year', 4)->nullable();
            $table->string('color', 50)->nullable();
            $table->string('chassis_no', 100)->nullable();
            $table->string('engine_no', 100)->nullable();
            $table->string('department_code', 50)->nullable();
            $table->string('person_in_charge', 255)->nullable();
            $table->string('staff_no_pic', 50)->nullable();
            $table->date('purchase_date')->nullable();
            $table->decimal('current_odometer', 10, 2)->nullable();
            $table->string('created_by', 50)->nullable();
            $table->timestamp('created_at')->nullable();
            $table->string('edited_by', 50)->nullable();
            $table->timestamp('edited_at')->nullable();
            $table->string('vehicle_status', 50)->default('ACTIVE');
            $table->string('status_data', 20)->default('ACTIVE');            
        });

        Schema::create('driver', function (Blueprint $table) {
            $table->id();
            $table->integer('vehicle_id')->unique();
            $table->string('staff_no', 50)->unique();
            $table->string('driver_name', 255);
            $table->string('position_name', 255)->nullable();
            $table->string('position_code', 50)->nullable();
            $table->string('department_name', 255)->nullable();
            $table->string('department_code', 50)->nullable();
            $table->string('email', 255)->nullable();
            $table->string('phone_no', 50)->nullable();
            $table->string('license_no', 100)->nullable();
            $table->date('license_expiry_date')->nullable();
            $table->string('created_by', 50)->nullable();
            $table->timestamp('created_at')->nullable();
            $table->string('edited_by', 50)->nullable();
            $table->timestamp('edited_at')->nullable();
            $table->string('driver_status', 50)->default('ACTIVE');
            $table->string('status_data', 20)->default('ACTIVE');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('vehicle');
        Schema::dropIfExists('vehicle');
    }
};