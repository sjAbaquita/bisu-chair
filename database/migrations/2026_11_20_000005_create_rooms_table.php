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
        Schema::create('rooms', function (Blueprint $table) {
            $table->id();
            $table->foreignId('building_id')->constrained()->cascadeOnDelete();
            $table->foreignId('college_id')->nullable()->constrained()->nullOnDelete();
            $table->string('code');
            $table->string('name')->nullable();
            $table->string('room_type');
            $table->integer('capacity')->nullable();
            $table->json('features')->nullable();
            $table->string('status')->default('available');
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->unique(['building_id', 'code']);
            $table->index('building_id');
            $table->index('college_id');
            $table->index('status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('rooms');
    }
};