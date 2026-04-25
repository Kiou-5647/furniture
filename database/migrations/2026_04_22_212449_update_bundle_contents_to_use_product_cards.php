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
        Schema::table('bundles', function (Blueprint $table) {
            $table->dropColumn('views_count');
            $table->dropColumn('reviews_count');
            $table->dropColumn('average_rating');
        });

        Schema::table('bundle_contents', function (Blueprint $table) {
            $table->foreignUuid('product_card_id')->after('bundle_id')->nullable()->constrained('product_cards')->onDelete('cascade');

            $table->dropForeign(['product_id']);
            $table->dropColumn('product_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('bundle_contents', function (Blueprint $table) {
            $table->foreignUuid('product_id')->after('bundle_id')->constrained('products')->onDelete('restrict');
            $table->dropForeign(['product_card_id']);
            $table->dropColumn('product_card_id');
        });

        Schema::table('bundles', function (Blueprint $table) {
            $table->unsignedInteger('views_count')->default(0)->check('view_count >= 0');
            $table->unsignedInteger('reviews_count')->default(0)->check('review_count >= 0');
            $table->decimal('average_rating', 3, 2)->default(0.0);
        });
    }
};
