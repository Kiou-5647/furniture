<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('provinces', function (Blueprint $table) {
            $table->string('province_code', 2)->primary();
            $table->string('name');
            $table->string('short_name')->nullable();
            $table->string('code', 10)->nullable();
            $table->string('place_type', 50)->nullable();
            $table->timestamps();
        });
        DB::statement('CREATE INDEX idx_provinces_trgm ON provinces USING GIN (name gin_trgm_ops)');

        Schema::create('wards', function (Blueprint $table) {
            $table->string('ward_code', 5)->primary();
            $table->string('province_code', 2);
            $table->string('name');
            $table->timestamps();
            $table->foreign('province_code')->references('province_code')->on('provinces')->onDelete('cascade');
        });
        DB::statement('CREATE INDEX idx_wards_trgm ON wards USING GIN (name gin_trgm_ops)');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('wards');
        Schema::dropIfExists('provinces');
    }
};
