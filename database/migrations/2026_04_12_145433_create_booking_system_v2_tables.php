<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('bookings', function (Blueprint $table) {
            $table->foreignUuid('deposit_invoice_id')->nullable()->after('accepted_by')->constrained('invoices')->onDelete('set null');
            $table->foreignUuid('final_invoice_id')->nullable()->after('deposit_invoice_id')->constrained('invoices')->onDelete('set null');
            $table->string('province_code', 10)->nullable()->after('shipping_address_id');
            $table->string('ward_code', 10)->nullable()->after('province_code');
            $table->string('province_name')->nullable()->after('ward_code');
            $table->string('ward_name')->nullable()->after('province_name');
            $table->jsonb('address_data')->nullable()->after('ward_name');

            $table->foreign('province_code')->references('province_code')->on('provinces')->onDelete('set null');
            $table->foreign('ward_code')->references('ward_code')->on('wards')->onDelete('set null');
        });
    }

    public function down(): void
    {
        //
    }
};
