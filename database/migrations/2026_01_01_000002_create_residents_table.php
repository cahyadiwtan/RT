<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('residents', function (Blueprint $table) {
            $table->id();
            $table->foreignId('house_id')->nullable()->constrained('houses')->nullOnDelete();
            $table->string('nik', 16)->unique();
            $table->string('nomor_kk', 16)->nullable();
            $table->string('nama_lengkap');
            $table->string('tempat_lahir')->nullable();
            $table->date('tanggal_lahir')->nullable();
            $table->enum('jenis_kelamin', ['L', 'P'])->default('L');
            $table->string('nomor_telepon', 20)->nullable();
            $table->string('email')->nullable();
            $table->enum('status_warga', ['aktif', 'pindah', 'meninggal', 'tidak_aktif'])->default('aktif');
            $table->enum('hubungan_dalam_keluarga', ['kepala_keluarga', 'istri', 'anak', 'famili_lain', 'lainnya'])->default('kepala_keluarga');
            $table->boolean('is_verified')->default(false);
            $table->timestamp('verified_at')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('residents');
    }
};
