<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::dropIfExists('designer_date_availabilities');
    }

    public function down(): void
    {
        Schema::create('designer_date_availabilities', function ($table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('designer_id')->constrained('designers')->onDelete('cascade');
            $table->date('date');
            $table->tinyInteger('start_hour');
            $table->tinyInteger('end_hour');
            $table->boolean('is_available')->default(true);
            $table->timestamps();
            $table->index(['designer_id', 'date']);
            $table->index('date');
        });
    }
};
