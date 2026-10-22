<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('user_log')) {
            Schema::create('user_log', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('user_id')->nullable();
                $table->string('nostaff', 50)->nullable();
                $table->string('nama', 255)->nullable();
                $table->string('action', 10);            // CREATE / EDIT / DELETE
                $table->string('table_name', 100);
                $table->text('query')->nullable();
                $table->text('bindings')->nullable();    // JSON
                $table->string('method', 10)->nullable();
                $table->string('url', 500)->nullable();
                $table->string('ip_address', 45)->nullable();
                $table->timestamp('created_at', 6)->useCurrent()->nullable();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('user_log');
    }
};
