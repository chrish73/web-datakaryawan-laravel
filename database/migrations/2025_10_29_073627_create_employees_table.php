<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('employees', function (Blueprint $table) {
            $table->id();
            $table->string('nik')->unique();
            $table->string('nama');
            $table->integer('usia')->nullable();
            $table->string('nama_unit');
            $table->string('lama_band_posisi')->nullable();
            $table->string('nilai_kinerja')->nullable();
            $table->string('nilai_kompetensi')->nullable();
            $table->string('nilai_behavior')->nullable();
            $table->string('tc')->nullable();
            $table->timestamps();
        });

    }

    public function down(): void
    {
        Schema::dropIfExists('employees');
    }
};
