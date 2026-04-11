<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->uuid('shipping_address_id')->nullable()->change();
            $table->string('province_code', 10)->nullable()->after('shipping_address_id');
            $table->string('ward_code', 10)->nullable()->after('province_code');
            $table->string('province_name')->nullable()->after('ward_code');
            $table->string('ward_name')->nullable()->after('province_name');
            $table->jsonb('address_data')->nullable()->after('ward_name');
        });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn(['province_code', 'ward_code', 'province_name', 'ward_name', 'address_data']);
        });
    }
};
