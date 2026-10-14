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
            $table->string('nostaff', 50)->unique();
            $table->string('nama', 255);
            $table->string('jawatan', 255)->nullable();
            $table->string('kod_jawatan', 50)->nullable();
            $table->string('jabatan', 255)->nullable();
            $table->string('kod_jabatan', 50)->nullable();
            $table->string('email', 255)->nullable()->unique();
            $table->string('token', 255)->nullable();
            $table->timestamp('token_expired_at', 6)->nullable();
            $table->string('remember_token', 100)->nullable();
            $table->timestamp('created_at', 6)->nullable();
            $table->timestamp('updated_at', 6)->nullable();
            $table->string('status_data', 20)->default('ACTIVE');
            $table->longText('module_role')->nullable();
            $table->id();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('users');
    }
};
