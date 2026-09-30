<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('event_participants', function (Blueprint $table) {
            $table->id();
            $table->foreignId('event_id')->constrained('events')->cascadeOnDelete();
            $table->foreignId('resident_id')->constrained('residents')->cascadeOnDelete();
            $table->enum('participation_status', ['invited', 'registered', 'confirmed', 'cancelled', 'attended', 'absent'])->default('registered');
            $table->decimal('payment_required', 12, 2)->default(0);
            $table->decimal('payment_amount', 12, 2)->default(0);
            $table->enum('payment_status', ['unpaid', 'partial', 'paid', 'waived'])->default('unpaid');
            $table->timestamp('registered_at')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('event_participants');
    }
};
