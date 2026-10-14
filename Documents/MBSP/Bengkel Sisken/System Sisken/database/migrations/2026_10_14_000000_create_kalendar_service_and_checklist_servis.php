<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    private const CHECKLIST = [
        'Bateri',
        'Radiator coolant',
        'Break tangan',
        'Break kaki',
        'Break angin',
        'Cermin sisi',
        'Cermin hadapan',
        'Minyak break',
        'Minyak enjin',
        'Minyak gear 90',
        'Minyak axle 140',
        'Minyak clutch',
        'Minyak hidraulik',
        'Minyak power steering',
        'Minyak TQ',
        'Lampu signal',
        'Lampu depan',
        'Lampu high beam',
        'Lampu break',
        'Lampu reverse',
        'Beacon',
        'Tayar depan',
        'Tayar belakang',
        'Hos',
        'Paip',
        'Body cabin',
        'Body chasis',
        'Silinder pam',
        'Timing belt dan belting',
        'Nombor plat',
        'Lain-lain',
    ];

    public function up(): void
    {
        // Replaces the earlier (empty) kalendar_maintenance table.
        Schema::dropIfExists('kalendar_maintenance');

        if (!Schema::hasTable('kalendar_service')) {
            Schema::create('kalendar_service', function (Blueprint $table) {
                $table->id();
                $table->date('tarikh');
                $table->smallInteger('tahun');
                $table->string('sesi', 10); // PAGI / PETANG
                $table->unsignedBigInteger('vehicle_id');
                $table->unsignedBigInteger('group_id')->nullable(); // MEKANIK_GROUP (SERVICE-A)
                $table->string('status_data', 20)->default('ACTIVE');
                $table->string('created_by', 100)->nullable();
                $table->timestamp('created_at', 6)->useCurrent()->nullable();
                $table->string('updated_by', 100)->nullable();
                $table->timestamp('updated_at', 6)->nullable();
                $table->index(['tahun', 'status_data']);
            });
        }

        if (!Schema::hasTable('checklist_servis')) {
            Schema::create('checklist_servis', function (Blueprint $table) {
                $table->id();
                $table->string('nama', 255);
                $table->smallInteger('susunan')->default(0);
                $table->string('status_data', 20)->default('ACTIVE');
                $table->string('created_by', 100)->nullable();
                $table->timestamp('created_at', 6)->useCurrent()->nullable();
                $table->string('updated_by', 100)->nullable();
                $table->timestamp('updated_at', 6)->nullable();
            });
        }

        $db = DB::connection(config('database.default'));

        if ($db->table('checklist_servis')->count() === 0) {
            foreach (self::CHECKLIST as $i => $nama) {
                $db->table('checklist_servis')->insert([
                    'nama' => $nama,
                    'susunan' => $i + 1,
                    'status_data' => 'ACTIVE',
                    'created_by' => 'SYSTEM',
                ]);
            }
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('checklist_servis');
        Schema::dropIfExists('kalendar_service');
    }
};
