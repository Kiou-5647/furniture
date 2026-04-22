<?php

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
        Schema::create('product_cards', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('product_id')->constrained()->onDelete('cascade');
            $table->unsignedBigInteger('views_count')->default(0);
            $table->unsignedBigInteger('reviews_count')->default(0);
            $table->decimal('average_rating', 3, 2)->default(0);
            $table->timestamps();

            $table->index('views_count');
            $table->index('average_rating');
        });

        Schema::create('product_card_options', function (Blueprint $table) {
            $table->id();
            $table->foreignUuid('product_card_id')->constrained()->onDelete('cascade');
            $table->foreignUuid('lookup_id')->constrained('lookups')->onDelete('cascade');

            $table->unique(['product_card_id', 'lookup_id']);
        });

        Schema::table('product_variants', function (Blueprint $table) {
            $table->foreignUuid('product_card_id')->after('product_id')->nullable()->constrained('product_cards')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('product_variants', function (Blueprint $table) {
            $table->dropColumn('product_card_id');
        });
        Schema::dropIfExists('product_card_options');
        Schema::dropIfExists('product_cards');
    }
};
