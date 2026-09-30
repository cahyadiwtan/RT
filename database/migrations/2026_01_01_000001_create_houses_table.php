<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('houses', function (Blueprint $table) {
            $table->id();
            $table->string('block', 10);
            $table->string('house_number', 10);
            $table->text('address')->nullable();
            $table->string('status', 20)->default('ditempati'); // ditempati, kosong, renovasi
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->unique(['block', 'house_number']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('houses');
    }
};
