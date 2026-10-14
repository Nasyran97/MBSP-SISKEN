<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('modules', function (Blueprint $table) {
            $table->id();
            $table->string('name', 255);
            $table->string('status_data', 20)->default('ACTIVE')->nullable();
        });

        Schema::create('roles', function (Blueprint $table) {
            $table->id();
            $table->string('name', 255);
            $table->unsignedBigInteger('modules_id')->nullable();
            $table->string('status_data', 20)->default('ACTIVE')->nullable();
            $table->foreign('modules_id')->references('id')->on('modules');
        });

        Schema::create('jabatan', function (Blueprint $table) {
            $table->smallInteger('depcode');
            $table->string('depname', 200)->nullable();
            $table->primary('depcode');
        });

        Schema::create('pembekal', function (Blueprint $table) {
            $table->id();
            $table->string('nama_pembekal', 255);
            $table->string('alamat_pembekal', 1000)->nullable();
            $table->string('created_by', 100)->nullable();
            $table->timestamp('created_at', 6)->useCurrent()->nullable();
            $table->string('updated_by', 100)->nullable();
            $table->timestamp('updated_at', 6)->nullable();
            $table->string('status_data', 20)->default('ACTIVE')->nullable();
        });

        Schema::create('kategori_penyelenggaraan', function (Blueprint $table) {
            $table->id();
            $table->string('kod_kategori', 50);
            $table->string('nama_kategori', 150);
            $table->string('mekanik_category', 50)->nullable();
            $table->char('is_mekanik_flow', 1)->default('Y');
            $table->char('auto_assign', 1)->default('N');
            $table->string('status_data', 20)->default('ACTIVE');
            $table->timestamp('created_at', 6)->useCurrent()->nullable();
            $table->string('created_by', 100)->nullable();
            $table->timestamp('updated_at', 6)->nullable();
            $table->string('updated_by', 100)->nullable();
        });

        Schema::create('insurans_panel', function (Blueprint $table) {
            $table->id();
            $table->string('panel_name', 255);
            $table->string('created_by', 100)->nullable();
            $table->timestamp('created_at', 6)->useCurrent()->nullable();
            $table->string('updated_by', 100)->nullable();
            $table->timestamp('updated_at', 6)->nullable();
            $table->string('status_data', 20)->default('ACTIVE')->nullable();
        });

        Schema::create('insurans_polisi', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('vehicle_id');
            $table->unsignedBigInteger('panel_id');
            $table->string('no_polisi', 100)->nullable();
            $table->dateTime('tarikh_mula_insurans')->nullable();
            $table->dateTime('tarikh_tamat_insurans')->nullable();
            $table->string('jenis_polisi', 100)->nullable();
            $table->decimal('ncd', 5, 2)->nullable();
            $table->string('created_by', 100)->nullable();
            $table->timestamp('created_at', 6)->useCurrent()->nullable();
            $table->string('updated_by', 100)->nullable();
            $table->timestamp('updated_at', 6)->nullable();
            $table->string('status_data', 20)->default('ACTIVE')->nullable();
            $table->string('polisi_status', 50)->default('AKTIF')->nullable();
        });

        Schema::create('liabiliti_awam', function (Blueprint $table) {
            $table->id();
            $table->string('tahun', 4)->nullable();
            $table->string('no_plat_harta_benda', 255)->nullable();
            $table->dateTime('tarikh_hantar_kes')->nullable();
            $table->unsignedBigInteger('tuntutan_awam_id')->nullable();
            $table->string('jenis_tuntutan', 255)->nullable();
            $table->string('nama_penuntut', 255)->nullable();
            $table->dateTime('tarikh_kejadian')->nullable();
            $table->decimal('amaun_tuntutan', 14, 2)->nullable();
            $table->string('no_telefon', 50)->nullable();
            $table->dateTime('tarikh_offer_letter')->nullable();
            $table->string('lokasi_kejadian', 1000)->nullable();
            $table->string('jabatan_kod', 50)->nullable();
            $table->string('jabatan_nama', 255)->nullable();
            $table->string('bahagian', 100)->nullable();
            $table->string('daerah_kejadian', 50)->nullable();
            $table->string('status_tuntutan', 100)->default('DALAM PROSES')->nullable();
            $table->string('keputusan_kes', 50)->nullable();
            $table->longText('laporan_adjuster_insurans')->nullable();
            $table->string('created_by', 100)->nullable();
            $table->timestamp('created_at', 6)->useCurrent()->nullable();
            $table->string('updated_by', 100)->nullable();
            $table->timestamp('updated_at', 6)->nullable();
            $table->string('status_data', 20)->default('ACTIVE')->nullable();
            $table->string('lampiran_file', 1000)->nullable();
        });

        Schema::create('tuntutan_awam', function (Blueprint $table) {
            $table->id();
            $table->string('tuntutan_name', 255);
            $table->string('created_by', 100)->nullable();
            $table->timestamp('created_at', 6)->useCurrent()->nullable();
            $table->string('updated_by', 100)->nullable();
            $table->timestamp('updated_at', 6)->nullable();
            $table->string('status_data', 20)->default('ACTIVE')->nullable();
        });

        Schema::create('tuntutan_mahkamah', function (Blueprint $table) {
            $table->id();
            $table->string('tuntutan_name', 255);
            $table->string('created_by', 100)->nullable();
            $table->timestamp('created_at', 6)->useCurrent()->nullable();
            $table->string('updated_by', 100)->nullable();
            $table->timestamp('updated_at', 6)->nullable();
            $table->string('status_data', 20)->default('ACTIVE')->nullable();
        });

        Schema::create('mahkamah_kes', function (Blueprint $table) {
            $table->id();
            $table->string('tahun', 4);
            $table->string('peguam_plaintif', 255)->nullable();
            $table->string('peguam_mbsp', 255)->nullable();
            $table->string('nama_penuntut', 255);
            $table->string('no_kenderaan_jenis', 255)->nullable();
            $table->longText('penyata_tuntutan')->nullable();
            $table->unsignedBigInteger('tuntutan_mahkamah_id');
            $table->string('jenis_tuntutan', 255)->nullable();
            $table->unsignedBigInteger('vehicle_id');
            $table->string('no_plat', 50)->nullable();
            $table->unsignedBigInteger('syarikat_insurans_id')->nullable();
            $table->string('syarikat_insurans', 255)->nullable();
            $table->string('jabatan_kod', 50);
            $table->string('jabatan_nama', 255)->nullable();
            $table->string('no_pekerja', 50);
            $table->string('nama_staf', 255)->nullable();
            $table->string('jawatan_staf', 255)->nullable();
            $table->string('daerah_kejadian', 50);
            $table->string('status_perbicaraan', 255)->nullable();
            $table->dateTime('tarikh_bicara_seterusnya')->nullable();
            $table->string('keputusan_kes', 255)->nullable();
            $table->string('created_by', 100)->nullable();
            $table->timestamp('created_at', 6)->useCurrent()->nullable();
            $table->string('updated_by', 100)->nullable();
            $table->timestamp('updated_at', 6)->nullable();
            $table->string('status_data', 20)->default('ACTIVE')->nullable();
            $table->longText('no_kes_mahkamah')->nullable();
            $table->string('status_kes', 50)->default('DALAM PROSES')->nullable();
            $table->string('file_upload', 1000)->nullable();
        });

        Schema::create('tuntutan_pihak_ketiga', function (Blueprint $table) {
            $table->id();
            $table->string('tuntutan_pihak_ketiga_name', 255);
            $table->string('created_by', 100)->nullable();
            $table->timestamp('created_at', 6)->useCurrent()->nullable();
            $table->string('updated_by', 100)->nullable();
            $table->timestamp('updated_at', 6)->nullable();
            $table->string('status_data', 20)->default('ACTIVE')->nullable();
        });

        Schema::create('mekanik_group', function (Blueprint $table) {
            $table->id();
            $table->string('category_code', 50);
            $table->string('group_code', 50);
            $table->string('group_name', 150);
            $table->integer('max_member')->default(0);
            $table->string('status_data', 20)->default('ACTIVE');
            $table->timestamp('created_at', 6)->useCurrent()->nullable();
            $table->string('created_by', 100)->nullable();
            $table->timestamp('updated_at', 6)->nullable();
            $table->string('updated_by', 100)->nullable();
        });

        Schema::create('mekanik_group_member', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('group_id');
            $table->unsignedBigInteger('user_id');
            $table->char('is_leader', 1)->default('N');
            $table->timestamp('joined_at', 6)->useCurrent();
            $table->timestamp('left_at', 6)->nullable();
            $table->string('status_data', 20)->default('ACTIVE');
            $table->timestamp('created_at', 6)->useCurrent()->nullable();
            $table->string('created_by', 100)->nullable();
            $table->timestamp('updated_at', 6)->nullable();
            $table->string('updated_by', 100)->nullable();
        });

        Schema::create('mekanik_individual_pool', function (Blueprint $table) {
            $table->id();
            $table->string('category_code', 50)->default('MAINTENANCE');
            $table->unsignedBigInteger('user_id');
            $table->char('auto_assign_enabled', 1)->default('Y');
            $table->string('status_data', 20)->default('ACTIVE');
            $table->timestamp('created_at', 6)->useCurrent()->nullable();
            $table->string('created_by', 100)->nullable();
            $table->timestamp('updated_at', 6)->nullable();
            $table->string('updated_by', 100)->nullable();
        });

        Schema::create('pengawas_vehicle', function (Blueprint $table) {
            $table->id();
            $table->timestamp('created_at', 6)->useCurrent()->nullable();
            $table->string('created_by', 100)->nullable();
            $table->timestamp('updated_at', 6)->nullable();
            $table->string('updated_by', 100)->nullable();
            $table->string('status_data', 20)->default('ACTIVE')->nullable();
            $table->unsignedBigInteger('vehicle_id')->nullable();
            $table->unsignedBigInteger('user_id')->nullable();
        });

        Schema::create('penyelenggaraan_assignment', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('penyelenggaraan_id');
            $table->unsignedBigInteger('arahan_kerja_id')->nullable();
            $table->string('category_code', 50);
            $table->string('assignment_type', 20);
            $table->unsignedBigInteger('group_id')->nullable();
            $table->unsignedBigInteger('user_id')->nullable();
            $table->string('status', 30)->default('ASSIGNED');
            $table->timestamp('assigned_at', 6)->useCurrent()->nullable();
            $table->string('assigned_by', 100)->nullable();
            $table->timestamp('started_at', 6)->nullable();
            $table->timestamp('completed_at', 6)->nullable();
            $table->string('status_data', 20)->default('ACTIVE');
            $table->timestamp('created_at', 6)->useCurrent()->nullable();
            $table->string('created_by', 100)->nullable();
            $table->timestamp('updated_at', 6)->nullable();
            $table->string('updated_by', 100)->nullable();
        });

        Schema::create('penyelenggaraan_assignment_member', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('assignment_id');
            $table->unsignedBigInteger('user_id');
            $table->string('nostaff_snapshot', 50)->nullable();
            $table->string('nama_snapshot', 300)->nullable();
            $table->string('jabatan_snapshot', 300)->nullable();
            $table->char('is_leader', 1)->default('N');
            $table->string('role_in_job', 50)->nullable();
            $table->timestamp('joined_group_at', 6)->nullable();
            $table->timestamp('assigned_at', 6)->useCurrent()->nullable();
            $table->timestamp('completed_at', 6)->nullable();
            $table->string('status', 30)->default('ASSIGNED');
            $table->string('status_data', 20)->default('ACTIVE');
            $table->timestamp('created_at', 6)->useCurrent()->nullable();
            $table->string('created_by', 100)->nullable();
        });

        Schema::create('penyelenggaraan_vehicle', function (Blueprint $table) {
            $table->id();
            $table->string('no_kerja', 100)->nullable();
            $table->string('jkjg_id', 100)->nullable();
            $table->unsignedBigInteger('vehicle_id');
            $table->unsignedBigInteger('driver_id')->nullable();
            $table->unsignedBigInteger('company_id')->nullable();
            $table->decimal('odometer', 12, 2)->nullable();
            $table->longText('repair_remark')->nullable();
            $table->string('location_name', 500)->nullable();
            $table->timestamp('created_at', 6)->useCurrent()->nullable();
            $table->string('created_by', 100)->nullable();
            $table->timestamp('updated_at', 6)->nullable();
            $table->string('updated_by', 100)->nullable();
            $table->string('status', 50)->nullable();
            $table->string('status_data', 20)->default('ACTIVE')->nullable();
            $table->string('driver_phone_num', 30)->nullable();
            $table->decimal('location_latitude', 10, 6)->nullable();
            $table->decimal('location_longitude', 10, 6)->nullable();
            $table->timestamp('pengesahan_at', 6)->nullable();
            $table->unsignedBigInteger('kategori_penyelenggaraan_id')->nullable();
            $table->longText('remark_done')->nullable();
            $table->longText('remark_transfer')->nullable();
            $table->timestamp('completed_at', 6)->nullable();
            $table->timestamp('transfer_at', 6)->nullable();
            $table->timestamp('pending_at', 6)->nullable();
        });

        Schema::create('vehicle_beli_temp', function (Blueprint $table) {
            $table->string('no_plat', 50)->nullable();
            $table->dateTime('tarikh_beli')->nullable();
            $table->decimal('harga_beli', 14, 2)->nullable();
            $table->string('nama_pembekal', 255)->nullable();
            $table->string('alamat_pembekal', 1000)->nullable();
        });

        Schema::create('vehicle_jenis_badan', function (Blueprint $table) {
            $table->id();
            $table->string('jenis_badan_name', 255);
            $table->string('created_by', 100)->nullable();
            $table->timestamp('created_at', 6)->useCurrent()->nullable();
            $table->string('updated_by', 100)->nullable();
            $table->timestamp('updated_at', 6)->nullable();
            $table->string('status_data', 20)->default('ACTIVE')->nullable();
        });

        Schema::create('vehicle_kelas_guna', function (Blueprint $table) {
            $table->id();
            $table->string('kelas_guna_name', 255);
            $table->string('created_by', 100)->nullable();
            $table->timestamp('created_at', 6)->useCurrent()->nullable();
            $table->string('updated_by', 100)->nullable();
            $table->timestamp('updated_at', 6)->nullable();
            $table->string('status_data', 20)->default('ACTIVE')->nullable();
        });

        Schema::create('vehicle_kemalangan', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('vehicle_id');
            $table->string('no_plat', 50)->nullable();
            $table->string('kod_jabatan_staf', 50)->nullable();
            $table->string('desc_jabatan_staf', 255)->nullable();
            $table->string('no_pekerja', 50)->nullable();
            $table->string('nama', 255)->nullable();
            $table->string('jawatan', 255)->nullable();
            $table->string('jabatan_kenderaan', 255)->nullable();
            $table->string('jenis_kenderaan', 255)->nullable();
            $table->decimal('ncd_semasa', 5, 2)->nullable();
            $table->string('baik_pulih_bengkel', 20)->nullable();
            $table->string('jenis_tuntutan_insurans', 100)->nullable();
            $table->string('bahagian', 50)->nullable();
            $table->string('daerah', 50)->nullable();
            $table->dateTime('tarikh_kemalangan')->nullable();
            $table->string('lokasi_kemalangan', 500)->nullable();
            $table->longText('keterangan_kemalangan')->nullable();
            $table->decimal('kos_baik_pulih', 12, 2)->nullable();
            $table->decimal('kos_tuntutan_pihak_ketiga', 12, 2)->nullable();
            $table->string('nama_penuntut', 255)->nullable();
            $table->string('no_plat_pihak_ketiga', 50)->nullable();
            $table->string('jenis_kenderaan_pihak_ketiga', 255)->nullable();
            $table->longText('kerosakan_pihak_ketiga')->nullable();
            $table->string('jenis_tuntutan_pihak_ketiga', 100)->nullable();
            $table->string('no_telefon_pihak_ketiga', 50)->nullable();
            $table->dateTime('tarikh_hantar_kes')->nullable();
            $table->string('tuntutan_gantirugi', 100)->nullable();
            $table->string('status_kes', 100)->nullable();
            $table->string('keputusan_kes_trafik', 100)->nullable();
            $table->longText('kemajuan_semasa')->nullable();
            $table->longText('laporan_adjuster_insurans')->nullable();
            $table->string('created_by', 100)->nullable();
            $table->timestamp('created_at', 6)->useCurrent()->nullable();
            $table->string('updated_by', 100)->nullable();
            $table->timestamp('updated_at', 6)->nullable();
            $table->string('status_data', 20)->default('ACTIVE')->nullable();
            $table->string('status_gantirugi', 50)->default('DALAM PROSES')->nullable();
            $table->string('status_repair', 50)->default('DALAM PROSES')->nullable();
            $table->string('lampiran_file', 1000)->nullable();
        });

        Schema::create('vehicle_lokasi_penempatan', function (Blueprint $table) {
            $table->id();
            $table->string('lokasi_penempatan_name', 255);
            $table->string('created_by', 100)->nullable();
            $table->timestamp('created_at', 6)->useCurrent()->nullable();
            $table->string('updated_by', 100)->nullable();
            $table->timestamp('updated_at', 6)->nullable();
            $table->string('status_data', 20)->default('ACTIVE')->nullable();
        });

        Schema::create('vehicle_lupus', function (Blueprint $table) {
            $table->id();
            $table->string('pembeli', 255)->nullable();
            $table->decimal('harga_jualan', 14, 2)->nullable();
            $table->dateTime('tarikh_lupus')->nullable();
            $table->string('kaedah_jualan', 50)->nullable();
            $table->string('cukai_kastam', 10)->nullable();
            $table->dateTime('tarikh_lelong')->nullable();
            $table->string('status_lupus', 20)->default('AKTIF')->nullable();
            $table->dateTime('tarikh_tukar_milik')->nullable();
            $table->dateTime('tarikh_puspakom')->nullable();
            $table->string('created_by', 100)->nullable();
            $table->timestamp('created_at', 6)->useCurrent()->nullable();
            $table->string('updated_by', 100)->nullable();
            $table->timestamp('updated_at', 6)->nullable();
            $table->string('status_data', 20)->default('ACTIVE')->nullable();
            $table->unsignedBigInteger('vehicle_id')->nullable();
        });

        Schema::create('vehicle_roadtax', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('vehicle_id');
            $table->dateTime('tarikh_mula')->nullable();
            $table->dateTime('tarikh_tamat')->nullable();
            $table->dateTime('jkptojpj')->nullable();
            $table->dateTime('jkptojab')->nullable();
            $table->string('file_path', 500)->nullable();
            $table->string('created_by', 100)->nullable();
            $table->timestamp('created_at', 6)->useCurrent()->nullable();
            $table->string('updated_by', 100)->nullable();
            $table->timestamp('updated_at', 6)->nullable();
            $table->string('status_data', 20)->default('ACTIVE')->nullable();
            $table->string('roadtax_status', 30)->default('ACTIVE')->nullable();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('vehicle_roadtax');
        Schema::dropIfExists('vehicle_lupus');
        Schema::dropIfExists('vehicle_lokasi_penempatan');
        Schema::dropIfExists('vehicle_kemalangan');
        Schema::dropIfExists('vehicle_kelas_guna');
        Schema::dropIfExists('vehicle_jenis_badan');
        Schema::dropIfExists('vehicle_beli_temp');
        Schema::dropIfExists('penyelenggaraan_vehicle');
        Schema::dropIfExists('penyelenggaraan_assignment_member');
        Schema::dropIfExists('penyelenggaraan_assignment');
        Schema::dropIfExists('pengawas_vehicle');
        Schema::dropIfExists('mekanik_individual_pool');
        Schema::dropIfExists('mekanik_group_member');
        Schema::dropIfExists('mekanik_group');
        Schema::dropIfExists('tuntutan_pihak_ketiga');
        Schema::dropIfExists('mahkamah_kes');
        Schema::dropIfExists('tuntutan_mahkamah');
        Schema::dropIfExists('tuntutan_awam');
        Schema::dropIfExists('liabiliti_awam');
        Schema::dropIfExists('insurans_polisi');
        Schema::dropIfExists('insurans_panel');
        Schema::dropIfExists('kategori_penyelenggaraan');
        Schema::dropIfExists('pembekal');
        Schema::dropIfExists('jabatan');
        Schema::dropIfExists('roles');
        Schema::dropIfExists('modules');
    }
};
