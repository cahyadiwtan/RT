<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('residents', function (Blueprint $table) {
            $table->string('nomor_ktp', 16)->nullable()->after('nik');
            $table->date('tanggal_kematian')->nullable()->after('tanggal_tinggal');
            $table->date('tanggal_pindah')->nullable()->after('tanggal_kematian');
            $table->enum('kewarganegaraan', ['WNI', 'WNA'])->default('WNI')->after('status_warga');
            $table->enum('domisili_asal', ['DD', 'LD'])->default('DD')->after('kewarganegaraan');
        });
    }

    public function down(): void
    {
        Schema::table('residents', function (Blueprint $table) {
            $table->dropColumn([
                'nomor_ktp',
                'tanggal_kematian',
                'tanggal_pindah',
                'kewarganegaraan',
                'domisili_asal',
            ]);
        });
    }
};
