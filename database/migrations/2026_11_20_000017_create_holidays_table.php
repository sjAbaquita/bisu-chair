<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('holidays', function (Blueprint $table) {
            $table->id();
            $table->foreignId('campus_id')->nullable()->constrained()->nullOnDelete();
            $table->date('date');
            $table->string('name');
            $table->boolean('is_class_day')->default(true);
            $table->timestamps();

            $table->index('campus_id');
            $table->index('date');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('holidays');
    }
};
