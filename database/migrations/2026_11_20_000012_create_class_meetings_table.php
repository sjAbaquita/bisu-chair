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
        Schema::create('class_meetings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('class_offering_id')->constrained()->cascadeOnDelete();
            $table->enum('delivery_mode', ['face_to_face', 'synchronous_online', 'asynchronous_online']);
            $table->foreignId('room_id')->nullable()->constrained()->nullOnDelete();
            $table->integer('day_of_week')->nullable();
            $table->time('start_time')->nullable();
            $table->time('end_time')->nullable();
            $table->date('effective_start_date');
            $table->date('effective_end_date');
            $table->string('online_platform')->nullable();
            $table->string('meeting_url')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index('class_offering_id');
            $table->index('delivery_mode');
            $table->index('room_id');
            $table->index('day_of_week');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('class_meetings');
    }
};