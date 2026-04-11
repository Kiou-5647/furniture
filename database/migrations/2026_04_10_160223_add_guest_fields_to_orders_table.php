<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->uuid('customer_id')->nullable()->change();
            $table->string('guest_name')->nullable()->after('customer_id');
            $table->string('guest_phone', 20)->nullable()->after('guest_name');
            $table->string('guest_email')->nullable()->after('guest_phone');
            $table->text('notes')->nullable()->after('shipping_address_id');
            $table->unsignedInteger('total_items')->default(0)->after('total_amount');
        });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->uuid('customer_id')->nullable(false)->change();
            $table->dropColumn(['guest_name', 'guest_phone', 'guest_email', 'notes', 'total_items']);
        });
    }
};
