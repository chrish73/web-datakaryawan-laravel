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
        // Diasumsikan tabel 'trainings' sudah ada
        Schema::table('trainings', function (Blueprint $table) {
            $table->date('tanggal_mulai')->nullable()->after('nama_pelatihan');
            $table->date('tanggal_selesai')->nullable()->after('tanggal_mulai');
            // Gunakan 'enum' untuk status agar data konsisten (Online atau Offline)
            $table->enum('status_pelatihan', ['Online', 'Offline'])->nullable()->after('tanggal_selesai');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('trainings', function (Blueprint $table) {
            $table->dropColumn('tanggal_mulai');
            $table->dropColumn('tanggal_selesai');
            $table->dropColumn('status_pelatihan');
        });
    }
};
