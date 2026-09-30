<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('aspirations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('resident_id')->constrained('residents')->cascadeOnDelete();
            $table->string('title');
            $table->text('description');
            $table->enum('category', ['keamanan', 'kebersihan', 'fasilitas', 'lingkungan', 'sosial', 'lainnya'])->default('lainnya');
            $table->enum('status', ['submitted', 'reviewed', 'in_progress', 'resolved', 'rejected'])->default('submitted');
            $table->string('attachment')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('aspirations');
    }
};
