<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->string('source', 50)->default('online')->after('status');
            $table->foreignUuid('store_location_id')
                ->nullable()
                ->after('source')
                ->constrained('locations')
                ->nullOnDelete();
            $table->timestamp('paid_at')->nullable()->after('total_amount');
            $table->decimal('shipping_cost', 15, 2)->default(0)->after('paid_at');
            $table->foreignUuid('shipping_method_id')
                ->nullable()
                ->after('shipping_cost')
                ->constrained('shipping_methods')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropForeign(['store_location_id']);
            $table->dropForeign(['shipping_method_id']);
            $table->dropColumn(['source', 'store_location_id', 'paid_at', 'shipping_cost', 'shipping_method_id']);
        });
    }
};
