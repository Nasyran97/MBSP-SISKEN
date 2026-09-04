<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('nostaff', 50)->unique();
            $table->string('nama', 255);
            $table->string('jawatan', 255)->nullable();
            $table->string('kod_jawatan', 50)->nullable();
            $table->string('jabatan', 255)->nullable();
            $table->string('kod_jabatan', 50)->nullable();
            $table->string('email', 255)->unique()->nullable();
            $table->text('modules')->nullable();
            $table->bigInteger('role_id')->nullable();
            $table->string('token', 255)->nullable();
            $table->timestamp('token_expired_at')->nullable();
            $table->rememberToken()->nullable();
            $table->timestamp('created_at')->nullable();
            $table->timestamp('updated_at')->nullable();
            $table->string('status_data', 20)->default('ACTIVE');
        });
    
        // Schema::create('roles', function (Blueprint $table) {
        //     $table->id();
        //     $table->string('role_code', 50)->unique();
        //     $table->string('role_name', 100);
        //     $table->string('status_data', 20)->default('ACTIVE');
        //     $table->timestamps();
        // });

        // Schema::create('modules', function (Blueprint $table) {
        //     $table->id();
        //     $table->string('module_code', 100)->unique();
        //     $table->string('module_name', 150);
        //     $table->string('status_data', 20)->default('ACTIVE');
        //     $table->timestamps();
        // });
    
        // Schema::create('role_modules', function (Blueprint $table) {
        //     $table->id();
        //     $table->bigInteger('role_id');
        //     $table->bigInteger('module_id');
        //     $table->string('status_data', 20)->default('ACTIVE');
        //     $table->timestamps();
    
        //     $table->unique(['role_id', 'module_id']);
        // });

        // Schema::create('sessions', function (Blueprint $table) {
        //     $table->string('id')->primary();
        //     $table->foreignId('user_id')->nullable()->index();
        //     $table->string('ip_address', 45)->nullable();
        //     $table->text('user_agent')->nullable();
        //     $table->longText('payload');
        //     $table->integer('last_activity')->index();
        // });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('users');
        // Schema::dropIfExists('roles');
        // Schema::dropIfExists('modules');
        // Schema::dropIfExists('role_modules');
        // Schema::dropIfExists('sessions');
    }
};
