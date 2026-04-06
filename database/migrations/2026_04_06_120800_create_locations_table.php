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
        Schema::create('locations', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('manager_id')->nullable();
            $table->string('code')->unique();
            $table->string('name');
            $table->string('type')->default('warehouse');
            $table->string('province_code', 2)->nullable();
            $table->string('ward_code', 5)->nullable();
            $table->string('province_name')->nullable();
            $table->string('ward_name')->nullable();
            $table->string('phone', 20)->nullable();
            $table->jsonb('address_data')->default('{}');
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('manager_id')->references('id')->on('employees')->onDelete('set null');
            $table->foreign('province_code')->references('province_code')->on('provinces')->onDelete('set null');
            $table->foreign('ward_code')->references('ward_code')->on('wards')->onDelete('set null');
        });
        DB::statement('CREATE INDEX idx_locations_province_code ON locations (province_code) WHERE deleted_at IS NULL;');
        DB::statement('CREATE INDEX idx_locations_type ON locations (type) WHERE deleted_at IS NULL;');
        DB::statement('CREATE INDEX idx_locations_active ON locations (is_active) WHERE deleted_at IS NULL;');
        DB::statement('CREATE INDEX idx_locations_manager_id ON locations (manager_id) WHERE deleted_at IS NULL;');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('locations');
    }
};
