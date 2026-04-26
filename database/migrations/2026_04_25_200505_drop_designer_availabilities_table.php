<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::dropIfExists('designer_availabilities');
    }

    public function down(): void
    {
        // To reverse this, you'd have to redefine the table from the old migration
        Schema::create('designer_availabilities', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('designer_id')->constrained('designers')->onDelete('cascade');
            $table->tinyInteger('day_of_week');
            $table->time('start_time');
            $table->time('end_time');
            $table->timestamps();
        });
    }
};
