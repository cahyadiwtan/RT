<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('assets', function (Blueprint $table) {
            $table->id();
            $table->string('asset_code')->unique(); // AST-0001
            $table->foreignId('category_id')->constrained('asset_categories')->cascadeOnDelete();
            $table->string('name');
            $table->text('description')->nullable();
            $table->integer('quantity')->default(1);
            $table->string('unit')->default('pcs');
            $table->enum('condition', ['baik', 'rusak_ringan', 'rusak_berat', 'tidak_layak', 'hilang'])->default('baik');
            $table->enum('status', ['tersedia', 'dipinjam', 'dalam_perbaikan', 'tidak_aktif', 'hilang'])->default('tersedia');
            $table->string('location')->nullable();
            $table->date('acquisition_date')->nullable();
            $table->string('acquisition_source')->nullable();
            $table->decimal('acquisition_price', 12, 2)->nullable();
            $table->decimal('current_value', 12, 2)->nullable();
            $table->string('brand')->nullable();
            $table->string('model')->nullable();
            $table->string('serial_number')->nullable();
            $table->string('photo')->nullable();
            $table->text('notes')->nullable();
            $table->foreignId('created_by')->constrained('users')->cascadeOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('assets');
    }
};
