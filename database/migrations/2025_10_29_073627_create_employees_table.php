<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('employees', function (Blueprint $table) {
            $table->id();

            // Kolom utama
            $table->string('tahun')->nullable();
            $table->string('bulan')->nullable();
            $table->string('nik')->unique();
            $table->string('nama')->nullable();
            $table->string('jenis_kelamin')->nullable();
            $table->string('nama_agama')->nullable();
            $table->integer('usia')->nullable();
            $table->string('kelompok_usia')->nullable();
            $table->date('tgl_capeg')->nullable();
            $table->date('tgl_pegprus')->nullable();
            $table->date('tgl_mulaikerja')->nullable();
            $table->date('tgl_pensiun')->nullable();

            $table->string('nama_employee_group')->nullable();
            $table->string('nama_employee_subgroup')->nullable();
            $table->string('kode_personnel_area')->nullable();
            $table->string('kode_host')->nullable();
            $table->string('kode_function_unit')->nullable();
            $table->string('nama_function_unit')->nullable();
            $table->string('kode_personnel_subarea')->nullable();
            $table->string('nama_personnel_subarea')->nullable();
            $table->date('tgl_psa')->nullable();
            $table->string('kode_payroll_area')->nullable();
            $table->string('nama_payroll_area')->nullable();
            $table->string('kode_divisi')->nullable();
            $table->date('tgl_divisi')->nullable();
            $table->string('nama_divisi')->nullable();
            $table->string('kode_unit')->nullable();
            $table->date('tgl_unit')->nullable();
            $table->string('nama_unit')->nullable();
            $table->string('long_unit')->nullable();
            $table->string('witel')->nullable();

            // Posisi
            $table->string('objidposisi')->nullable();
            $table->date('tgl_posisi')->nullable();
            $table->string('kode_posisi')->nullable();
            $table->string('nama_posisi')->nullable();
            $table->string('long_posisi')->nullable();
            $table->integer('lama_posisi')->nullable();
            $table->string('nama_action')->nullable();
            $table->string('band_posisi')->nullable();
            $table->date('tgl_band_posisi')->nullable();
            $table->integer('lama_band_posisi')->nullable();
            $table->string('flag_pj')->nullable();
            $table->integer('lama_pj')->nullable();

            // Nilai
            $table->string('tahun_kinerja')->nullable();
            $table->string('nilai_kinerja')->nullable();
            $table->string('tahun_kompetensi')->nullable();
            $table->string('nilai_kompetensi')->nullable();
            $table->string('tahun_behavior')->nullable();
            $table->string('nilai_behavior')->nullable();

            // Pendidikan
            $table->string('level_pendidikan')->nullable();
            $table->string('group_pendidikan')->nullable();
            $table->string('jurusan_pendidikan')->nullable();
            $table->string('nama_institusi')->nullable();

            // Perusahaan
            $table->string('kode_perusahaan')->nullable();
            $table->string('nama_perusahaan')->nullable();
            $table->string('kode_home')->nullable();
            $table->string('nama_home')->nullable();

            // Job Info
            $table->string('job_family')->nullable();
            $table->string('job_function')->nullable();
            $table->string('job_role')->nullable();
            $table->string('role_category')->nullable();
            $table->string('flag_chief')->nullable();

            // Info tambahan
            $table->string('email')->nullable();
            $table->string('kode_gedung')->nullable();
            $table->string('nama_gedung')->nullable();
            $table->string('alamat_gedung')->nullable();
            $table->string('kota_gedung')->nullable();
            $table->string('unit')->nullable();
            $table->string('tc')->nullable();
            $table->string('status_eligibility')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('employees');
    }
};
