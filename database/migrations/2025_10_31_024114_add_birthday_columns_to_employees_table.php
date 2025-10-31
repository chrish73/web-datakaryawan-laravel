<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('employees', function (Blueprint $table) {
            $table->date('tgl_lahir')->nullable()->after('nama');
            $table->string('kota_lahir')->nullable()->after('tgl_lahir');
            $table->string('nama_jalan')->nullable()->after('kota_lahir');
        });
    }

    public function down(): void
    {
        Schema::table('employees', function (Blueprint $table) {
            $table->dropColumn(['tgl_lahir', 'kota_lahir', 'nama_jalan']);
        });
    }
};
