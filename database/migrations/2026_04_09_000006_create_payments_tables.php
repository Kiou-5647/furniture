<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('invoices', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('invoice_number')->unique();
            $table->string('invoiceable_type', 100);
            $table->uuid('invoiceable_id');
            $table->string('type', 50)->comment('deposit, final_balance, full');
            $table->decimal('amount_due', 15, 2)->default(0);
            $table->decimal('amount_paid', 15, 2)->default(0);
            $table->string('status', 50)->default('draft')->comment('draft, open, paid');
            $table->foreignUuid('validated_by')->nullable()->constrained('employees')->onDelete('set null');
            $table->timestamps();
            $table->softDeletes();
        });

        DB::statement("CREATE INDEX idx_invoices_invoiceable ON invoices (invoiceable_type, invoiceable_id)");
        DB::statement("CREATE INDEX idx_invoices_status ON invoices (status)");

        Schema::create('payments', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('customer_id')->constrained('users')->onDelete('restrict');
            $table->string('gateway', 50)->comment('vnpay, momo, zalopay, stripe, manual');
            $table->string('transaction_id')->nullable();
            $table->decimal('amount', 15, 2)->default(0);
            $table->string('status', 50)->default('pending')->comment('pending, successful, failed, refunded');
            $table->jsonb('gateway_payload')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        DB::statement("CREATE INDEX idx_payments_customer_id ON payments (customer_id)");
        DB::statement("CREATE INDEX idx_payments_transaction_id ON payments (transaction_id) WHERE transaction_id IS NOT NULL");
        DB::statement("CREATE INDEX idx_payments_status ON payments (status)");

        Schema::create('payment_allocations', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('payment_id')->constrained('payments')->onDelete('cascade');
            $table->foreignUuid('invoice_id')->constrained('invoices')->onDelete('cascade');
            $table->decimal('amount_applied', 15, 2)->default(0);
            $table->timestamps();
        });

        DB::statement("CREATE INDEX idx_payment_allocations_payment_id ON payment_allocations (payment_id)");
        DB::statement("CREATE INDEX idx_payment_allocations_invoice_id ON payment_allocations (invoice_id)");
    }

    public function down(): void
    {
        Schema::dropIfExists('payment_allocations');
        Schema::dropIfExists('payments');
        Schema::dropIfExists('invoices');
    }
};
