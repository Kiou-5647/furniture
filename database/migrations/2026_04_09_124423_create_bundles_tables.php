<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('bundles', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('name');
            $table->string('slug', 255);
            $table->text('description')->nullable();
            $table->string('discount_type', 50)->nullable()->comment('percentage, fixed_amount, fixed_price');
            $table->decimal('discount_value', 15, 2)->nullable();
            $table->boolean('is_active')->default(true);
            $table->unsignedInteger('views_count')->default(0)->check('view_count >= 0');
            $table->unsignedInteger('reviews_count')->default(0)->check('review_count >= 0');
            $table->decimal('average_rating', 3, 2)->default(0.0);
            $table->timestamps();
            $table->softDeletes();
        });

        // Partial unique index for slug where active and not deleted
        DB::statement('CREATE UNIQUE INDEX unq_bundles_slug_active ON bundles (slug) WHERE deleted_at IS NULL');
        DB::statement('CREATE INDEX idx_bundles_active ON bundles (is_active) WHERE is_active = true AND deleted_at IS NULL');
        DB::statement('CREATE INDEX idx_bundles_created_at ON bundles (created_at)');
        DB::statement('CREATE INDEX idx_bundles_views_published ON bundles (views_count DESC) WHERE is_active = true AND deleted_at IS NULL');
        DB::statement('CREATE INDEX idx_bundles_rating_published ON bundles (average_rating DESC) WHERE is_active = true AND deleted_at IS NULL');

        Schema::create('bundle_contents', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('bundle_id')->constrained('bundles')->onDelete('cascade');
            $table->foreignUuid('product_id')->constrained('products')->onDelete('restrict');
            $table->integer('quantity')->default(1);
            $table->timestamps();
        });

        DB::statement('CREATE INDEX idx_bundle_contents_bundle_id ON bundle_contents (bundle_id)');
        DB::statement('CREATE INDEX idx_bundle_contents_product_id ON bundle_contents (product_id)');
    }

    public function down(): void
    {
        Schema::dropIfExists('bundle_contents');
        Schema::dropIfExists('bundles');
    }
};
