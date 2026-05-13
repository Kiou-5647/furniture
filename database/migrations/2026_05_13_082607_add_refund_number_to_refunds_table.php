<?php

use App\Models\Sales\Refund;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('refunds', function (Blueprint $table) {
            $table->string('refund_number')->nullable()->after('id')->unique();
        });

        Refund::all()->each(function ($refund) {
            $refund->update([
                'refund_number' => $refund->generateRefundNumber()
            ]);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('refunds', function (Blueprint $table) {
            $table->dropColumn('refund_number');
        });
    }
};
