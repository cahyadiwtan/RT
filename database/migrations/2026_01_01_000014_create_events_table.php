<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('events', function (Blueprint $table) {
            $table->id();
            $table->string('event_code')->unique(); // EVT-2026-001
            $table->string('title');
            $table->string('slug')->unique();
            $table->text('description')->nullable();
            $table->string('category')->nullable();
            $table->string('location')->nullable();
            $table->dateTime('start_at')->nullable();
            $table->dateTime('end_at')->nullable();
            $table->dateTime('registration_deadline')->nullable();
            $table->dateTime('payment_deadline')->nullable();
            $table->enum('funding_type', ['free', 'resident_fee', 'voluntary', 'rt_fund', 'mixed'])->default('free');
            $table->decimal('required_payment', 12, 2)->default(0);
            $table->decimal('target_amount', 12, 2)->default(0);
            $table->enum('status', ['draft', 'published', 'registration_open', 'ongoing', 'completed', 'cancelled', 'closed'])->default('draft');
            $table->enum('visibility', ['public', 'resident_only', 'names_only', 'summary', 'private'])->default('resident_only');
            $table->string('cover_image')->nullable();
            $table->foreignId('created_by')->constrained('users')->cascadeOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('events');
    }
};
