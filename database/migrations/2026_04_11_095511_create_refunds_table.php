<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('refunds', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('order_id')->constrained('orders')->onDelete('cascade');
            $table->foreignUuid('payment_id')->nullable()->constrained('payments')->onDelete('set null');
            $table->decimal('amount', 15, 2);
            $table->text('reason')->nullable();
            $table->string('status', 20)->default('pending');
            $table->foreignUuid('requested_by')->constrained('employees')->onDelete('restrict');
            $table->foreignUuid('processed_by')->nullable()->constrained('employees')->nullOnDelete();
            $table->text('notes')->nullable();
            $table->timestamp('processed_at')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('refunds');
    }
};
