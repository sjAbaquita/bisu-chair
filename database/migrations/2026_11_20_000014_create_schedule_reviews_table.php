<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('schedule_reviews', function (Blueprint $table) {
            $table->id();
            $table->foreignId('class_offering_id')->constrained()->cascadeOnDelete();
            $table->foreignId('reviewer_id')->constrained('users')->cascadeOnDelete();
            $table->enum('action', ['submitted', 'approved', 'returned', 'rejected']);
            $table->text('comment')->nullable();
            $table->timestamp('acted_at')->nullable();
            $table->timestamps();

            $table->index(['class_offering_id', 'action']);
            $table->index('reviewer_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('schedule_reviews');
    }
};
