<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('refunds', function (Blueprint $table) {
            $table->foreignUuid('order_id')->nullable()->change();
            $table->foreignUuid('invoice_id')->nullable()->after('order_id')->constrained('invoices')->onDelete('cascade');
            $table->foreignUuid('booking_id')->nullable()->after('invoice_id')->constrained('bookings')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::table('refunds', function (Blueprint $table) {
            $table->dropForeign(['booking_id']);
            $table->dropColumn('booking_id');
            $table->dropForeign(['invoice_id']);
            $table->dropColumn('invoice_id');
            $table->foreignUuid('order_id')->nullable(false)->change();
        });
    }
};
