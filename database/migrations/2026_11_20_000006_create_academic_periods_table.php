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
        Schema::create('academic_periods', function (Blueprint $table) {
            $table->id();
            $table->string('academic_year');
            $table->string('term');
            $table->date('start_date');
            $table->date('end_date');
            $table->string('status')->default('draft');
            $table->boolean('is_current')->default(false);
            $table->timestamps();

            $table->index('academic_year');
            $table->index('term');
            $table->index('status');
            $table->index('is_current');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('academic_periods');
    }
};