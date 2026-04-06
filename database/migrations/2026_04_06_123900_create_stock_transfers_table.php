<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('stock_transfers', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('transfer_number')->unique();
            $table->uuid('from_location_id');
            $table->uuid('to_location_id');
            $table->string('status')->default('draft');
            $table->uuid('requested_by')->nullable();
            $table->uuid('received_by')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->softDeletes();
            $table->timestamp('received_at')->nullable();

            $table->foreign('from_location_id')->references('id')->on('locations')->onDelete('restrict');
            $table->foreign('to_location_id')->references('id')->on('locations')->onDelete('restrict');
            $table->foreign('requested_by')->references('id')->on('employees')->onDelete('set null');
            $table->foreign('received_by')->references('id')->on('employees')->onDelete('set null');
        });

        Schema::create('stock_transfer_items', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('transfer_id');
            $table->uuid('variant_id');
            $table->unsignedInteger('quantity_shipped');
            $table->unsignedInteger('quantity_received')->default(0);
            $table->timestamps();

            $table->foreign('transfer_id')->references('id')->on('stock_transfers')->onDelete('cascade');
            $table->foreign('variant_id')->references('id')->on('product_variants')->onDelete('restrict');
        });

        DB::statement('CREATE INDEX idx_stock_transfers_status ON stock_transfers (status);');
        DB::statement('CREATE INDEX idx_stock_transfers_from_location ON stock_transfers (from_location_id);');
        DB::statement('CREATE INDEX idx_stock_transfers_to_location ON stock_transfers (to_location_id);');
        DB::statement('CREATE INDEX idx_stock_transfers_requested_by ON stock_transfers (requested_by);');
        DB::statement('CREATE INDEX idx_stock_transfers_received_by ON stock_transfers (received_by);');
        DB::statement('CREATE INDEX idx_stock_transfer_items_transfer_id ON stock_transfer_items (transfer_id);');
        DB::statement('CREATE INDEX idx_stock_transfer_items_variant_id ON stock_transfer_items (variant_id);');
    }

    public function down(): void
    {
        Schema::dropIfExists('stock_transfer_items');
        Schema::dropIfExists('stock_transfers');
    }
};
