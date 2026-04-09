<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('shipping_methods', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('code')->unique();
            $table->string('name');
            $table->decimal('price', 15, 2)->default(0);
            $table->integer('estimated_delivery_days')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->softDeletes();
        });

        DB::statement("CREATE INDEX idx_shipping_methods_active ON shipping_methods (is_active) WHERE is_active = true AND deleted_at IS NULL");

        Schema::create('shipments', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('order_id')->constrained('orders')->onDelete('cascade');
            $table->string('shipment_number')->unique();
            $table->foreignUuid('vendor_id')->nullable()->constrained('vendors')->onDelete('restrict');
            $table->foreignUuid('origin_location_id')->nullable()->constrained('locations')->onDelete('set null');
            $table->foreignUuid('shipping_method_id')->nullable()->constrained('shipping_methods')->onDelete('restrict');
            $table->string('carrier', 100)->nullable();
            $table->string('tracking_number')->nullable();
            $table->string('status', 50)->default('pending');
            $table->foreignUuid('handled_by')->nullable()->comment('References either employee_id or vendor_user_id. Can be polymorphic, but logically mapped at app level')->constrained('users')->onDelete('set null');
            $table->timestamps();
            $table->softDeletes();
        });

        DB::statement("CREATE INDEX idx_shipments_order_id ON shipments (order_id)");
        DB::statement("CREATE INDEX idx_shipments_vendor_id ON shipments (vendor_id) WHERE vendor_id IS NOT NULL");
        DB::statement("CREATE INDEX idx_shipments_status ON shipments (status)");
        DB::statement("CREATE INDEX idx_shipments_tracking ON shipments (tracking_number) WHERE tracking_number IS NOT NULL");

        Schema::create('shipment_items', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('shipment_id')->constrained('shipments')->onDelete('cascade');
            $table->foreignUuid('order_item_id')->constrained('order_items')->onDelete('cascade');
            $table->integer('quantity_shipped')->default(0);
            $table->timestamps();
        });

        DB::statement("CREATE INDEX idx_shipment_items_shipment_id ON shipment_items (shipment_id)");
        DB::statement("CREATE INDEX idx_shipment_items_order_item_id ON shipment_items (order_item_id)");
    }

    public function down(): void
    {
        Schema::dropIfExists('shipment_items');
        Schema::dropIfExists('shipments');
        Schema::dropIfExists('shipping_methods');
    }
};
