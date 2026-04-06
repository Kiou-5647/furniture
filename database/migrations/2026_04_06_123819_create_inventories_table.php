<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('inventories', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('variant_id');
            $table->uuid('location_id');
            $table->unsignedInteger('quantity_on_hand')->default(0);
            $table->unsignedInteger('quantity_reserved')->default(0);
            $table->unsignedInteger('quantity_available')->default(0);
            $table->unsignedInteger('reorder_level')->default(5);
            $table->unsignedInteger('reorder_quantity')->default(10);
            $table->decimal('cost_per_unit', 15, 2)->default(0);
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('variant_id')->references('id')->on('product_variants')->onDelete('cascade');
            $table->foreign('location_id')->references('id')->on('locations')->onDelete('cascade');

            $table->unique(['variant_id', 'location_id']);
        });

        DB::statement('CREATE INDEX idx_inventories_variant_id ON inventories (variant_id);');
        DB::statement('CREATE INDEX idx_inventories_location_id ON inventories (location_id);');
        DB::statement('CREATE INDEX idx_inventories_low_stock ON inventories (quantity_available, reorder_level) WHERE quantity_available <= reorder_level;');
    }

    public function down(): void
    {
        Schema::dropIfExists('inventories');
    }
};
