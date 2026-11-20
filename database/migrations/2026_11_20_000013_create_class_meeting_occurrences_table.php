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
        Schema::create('class_meeting_occurrences', function (Blueprint $table) {
            $table->id();
            $table->foreignId('class_meeting_id')->constrained()->cascadeOnDelete();
            $table->date('meeting_date');
            $table->time('start_time');
            $table->time('end_time');
            $table->foreignId('room_id')->nullable()->constrained()->nullOnDelete();
            $table->enum('status', ['active', 'cancelled', 'rescheduled'])->default('active');
            $table->timestamps();

            $table->index('meeting_date');
            $table->index('room_id');
            $table->index('start_time');
            $table->index('end_time');
            $table->index('status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('class_meeting_occurrences');
    }
};