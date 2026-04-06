<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('stock_valuations', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('variant_id');
            $table->uuid('location_id');
            $table->decimal('batch_cost', 15, 2);
            $table->unsignedInteger('quantity_remaining');
            $table->timestamp('received_at');
            $table->uuid('reference_id')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('variant_id')->references('id')->on('product_variants')->onDelete('cascade');
            $table->foreign('location_id')->references('id')->on('locations')->onDelete('cascade');
        });

        DB::statement('CREATE INDEX idx_stock_valuations_variant_location ON stock_valuations (variant_id, location_id);');
        DB::statement('CREATE INDEX idx_stock_valuations_remaining ON stock_valuations (quantity_remaining) WHERE quantity_remaining > 0;');
    }

    public function down(): void
    {
        Schema::dropIfExists('stock_valuations');
    }
};
