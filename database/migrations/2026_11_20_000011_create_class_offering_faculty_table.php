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
        Schema::create('class_offering_faculty', function (Blueprint $table) {
            $table->id();
            $table->foreignId('class_offering_id')->constrained()->cascadeOnDelete();
            $table->foreignId('faculty_id')->constrained()->cascadeOnDelete();
            $table->decimal('load_hours', 5, 2)->nullable();
            $table->decimal('load_units', 5, 2)->nullable();
            $table->string('role')->nullable();
            $table->timestamps();

            $table->unique(['class_offering_id', 'faculty_id']);
            $table->index('faculty_id');
            $table->index('class_offering_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('class_offering_faculty');
    }
};