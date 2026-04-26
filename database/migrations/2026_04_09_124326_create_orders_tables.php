<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('orders', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('order_number')->unique();
            $table->foreignUuid('customer_id')->constrained('users')->onDelete('restrict');
            $table->uuid('shipping_address_id')->nullable();
            $table->decimal('total_amount', 15, 2)->default(0);
            $table->string('status', 50)->default('pending');
            $table->foreignUuid('accepted_by')->nullable()->constrained('employees')->onDelete('set null');
            $table->timestamps();
            $table->softDeletes();
        });

        DB::statement('CREATE INDEX idx_orders_customer_id ON orders (customer_id)');
        DB::statement('CREATE INDEX idx_orders_status ON orders (status)');
        DB::statement('CREATE INDEX idx_orders_created_at ON orders (created_at)');

        Schema::create('order_items', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('order_id')->constrained('orders')->onDelete('cascade');
            $table->string('purchasable_type', 100);
            $table->uuid('purchasable_id');
            $table->integer('quantity')->default(1);
            $table->decimal('unit_price', 15, 2)->default(0);
            $table->jsonb('configuration')->default('{}');
            $table->timestamps();
        });

        DB::statement('CREATE INDEX idx_order_items_order_id ON order_items (order_id)');
        DB::statement('CREATE INDEX idx_order_items_purchasable ON order_items (purchasable_type, purchasable_id)');
    }

    public function down(): void
    {
        Schema::dropIfExists('order_items');
        Schema::dropIfExists('orders');
    }
};
