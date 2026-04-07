<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('stock_movements', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('variant_id');
            $table->uuid('location_id');
            $table->string('type');
            $table->integer('quantity');
            $table->unsignedInteger('quantity_before')->default(0);
            $table->unsignedInteger('quantity_after')->default(0);
            $table->decimal('cost_per_unit', 15, 2)->nullable();
            $table->decimal('cost_per_unit_before', 15, 2)->nullable();
            $table->string('reference_type')->nullable();
            $table->uuid('reference_id')->nullable();
            $table->text('notes')->nullable();
            $table->uuid('performed_by')->nullable();
            $table->timestamps();

            $table->foreign('variant_id')->references('id')->on('product_variants')->onDelete('cascade');
            $table->foreign('location_id')->references('id')->on('locations')->onDelete('cascade');
            $table->foreign('performed_by')->references('id')->on('employees')->onDelete('set null');
        });

        DB::statement('CREATE INDEX idx_stock_movements_variant_id ON stock_movements (variant_id);');
        DB::statement('CREATE INDEX idx_stock_movements_location_id ON stock_movements (location_id);');
        DB::statement('CREATE INDEX idx_stock_movements_type ON stock_movements (type);');
        DB::statement('CREATE INDEX idx_stock_movements_reference ON stock_movements (reference_type, reference_id);');
        DB::statement('CREATE INDEX idx_stock_movements_created_at ON stock_movements (created_at DESC);');
    }

    public function down(): void
    {
        Schema::dropIfExists('stock_movements');
    }
};
