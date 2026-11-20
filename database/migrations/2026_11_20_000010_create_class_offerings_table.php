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
        Schema::create('class_offerings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('academic_period_id')->constrained()->cascadeOnDelete();
            $table->foreignId('department_id')->constrained()->cascadeOnDelete();
            $table->foreignId('section_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('subject_id')->constrained();
            $table->enum('delivery_mode', ['face_to_face', 'online', 'hybrid']);
            $table->integer('class_size')->nullable();
            $table->enum('status', ['draft', 'submitted', 'for_review', 'returned', 'approved', 'cancelled', 'archived'])->default('draft');
            $table->text('remarks')->nullable();
            $table->foreignId('created_by')->constrained('users');
            $table->foreignId('submitted_by')->nullable()->constrained('users');
            $table->foreignId('approved_by')->nullable()->constrained('users');
            $table->timestamp('submitted_at')->nullable();
            $table->timestamp('approved_at')->nullable();
            $table->softDeletes();
            $table->timestamps();

            $table->index('academic_period_id');
            $table->index('department_id');
            $table->index('subject_id');
            $table->index('delivery_mode');
            $table->index('status');
            $table->index('created_by');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('class_offerings');
    }
};