<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('carts', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('user_id')->nullable()->constrained('users')->onDelete('set null');
            $table->string('session_id')->nullable();
            $table->string('status', 50)->default('open');
            $table->timestamps();
        });

        DB::statement('CREATE INDEX idx_carts_user_id ON carts (user_id) WHERE user_id IS NOT NULL');
        DB::statement('CREATE INDEX idx_carts_session_id ON carts (session_id) WHERE session_id IS NOT NULL');
        DB::statement('CREATE INDEX idx_carts_status ON carts (status)');

        Schema::create('cart_items', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('cart_id')->constrained('carts')->onDelete('cascade');
            $table->string('purchasable_type', 100);
            $table->uuid('purchasable_id');
            $table->integer('quantity')->default(1);
            $table->jsonb('configuration')->default('{}');
            $table->timestamps();

            // Note: Not foreign keying purchasable_id directly since it's polymorphic (products or bundles)
        });

        DB::statement('CREATE INDEX idx_cart_items_cart_id ON cart_items (cart_id)');
        DB::statement('CREATE INDEX idx_cart_items_purchasable ON cart_items (purchasable_type, purchasable_id)');
    }

    public function down(): void
    {
        Schema::dropIfExists('cart_items');
        Schema::dropIfExists('carts');
    }
};
