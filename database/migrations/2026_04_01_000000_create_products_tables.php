<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('products', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('vendor_id')->nullable()->constrained('vendors')->restrictOnDelete();
            $table->foreignUuid('category_id')->nullable()->constrained('categories')->restrictOnDelete();
            $table->foreignUuid('collection_id')->nullable()->constrained('collections')->nullOnDelete();

            $table->string('status', 20)->default('draft');
            $table->string('name');

            $table->decimal('min_price', 12, 2)->default(0);
            $table->decimal('max_price', 12, 2)->default(0);

            $table->jsonb('features')->default('[]');
            $table->jsonb('specifications')->default('{}');
            $table->jsonb('option_groups')->default('[]');
            $table->jsonb('filterable_options')->default('{}');
            $table->jsonb('care_instructions')->default('[]');
            $table->jsonb('assembly_info')->default('{}');

            $table->boolean('is_custom_made')->default(false);

            $table->integer('warranty_months')->nullable();
            $table->integer('view_count')->default(0)->check('view_count >= 0');
            $table->unsignedInteger('review_count')->default(0);
            $table->decimal('average_rating', 3, 2)->nullable();

            $table->boolean('is_featured')->default(false);
            $table->boolean('is_new_arrival')->default(false);

            $table->timestamp('published_date')->nullable();
            $table->timestamp('new_arrival_until')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
        DB::statement('CREATE INDEX idx_products_trgm ON products USING GIN (name gin_trgm_ops)');
        DB::statement('CREATE INDEX idx_products_filterable_options ON products USING GIN (filterable_options)');
        DB::statement('CREATE INDEX idx_products_status ON products (status) WHERE deleted_at IS NULL');
        DB::statement('CREATE INDEX idx_products_deleted ON products (deleted_at) WHERE deleted_at IS NOT NULL');
        DB::statement('CREATE INDEX idx_products_vendor_id ON products (vendor_id) WHERE deleted_at IS NULL');
        DB::statement('CREATE INDEX idx_products_category_id ON products (category_id) WHERE deleted_at IS NULL');
        DB::statement('CREATE INDEX idx_products_collection_id ON products (collection_id) WHERE deleted_at IS NULL');
        DB::statement('CREATE INDEX idx_products_category_status ON products (category_id, status) WHERE deleted_at IS NULL');
        DB::statement('CREATE INDEX idx_products_vendor_status ON products (vendor_id, status) WHERE deleted_at IS NULL');
        DB::statement('CREATE INDEX idx_products_collection_status ON products (collection_id, status) WHERE deleted_at IS NULL');
        DB::statement('CREATE INDEX idx_products_created_at ON products (created_at DESC) WHERE deleted_at IS NULL');
        DB::statement('CREATE INDEX idx_products_published_date ON products (published_date DESC) WHERE deleted_at IS NULL AND published_date IS NOT NULL');
        DB::statement('CREATE INDEX idx_products_rating ON products (average_rating DESC) WHERE deleted_at IS NULL AND average_rating IS NOT NULL');
        DB::statement('CREATE INDEX idx_products_min_price ON products (min_price) WHERE deleted_at IS NULL');
        DB::statement('CREATE INDEX idx_products_max_price ON products (max_price) WHERE deleted_at IS NULL');
        DB::statement('CREATE INDEX idx_products_is_featured ON products (is_featured) WHERE deleted_at IS NULL AND is_featured = true');
        DB::statement('CREATE INDEX idx_products_is_new_arrival ON products (is_new_arrival) WHERE deleted_at IS NULL AND is_new_arrival = true');

        Schema::create('product_variants', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('product_id')->constrained('products')->onDelete('cascade');

            $table->string('sku', 20);
            $table->string('status', 20)->default('active');

            $table->string('name')->nullable();
            $table->string('swatch_label')->nullable();
            $table->string('slug', 128)->nullable();
            $table->text('description')->nullable();

            $table->decimal('price', 15, 2);
            $table->decimal('profit_margin_value', 15, 2)->nullable();
            $table->string('profit_margin_unit', 20)->default('fixed');

            $table->jsonb('weight')->default('{}');
            $table->jsonb('dimensions')->default('{}');
            $table->jsonb('option_values')->default('{}');
            $table->jsonb('features')->default('[]');
            $table->jsonb('specifications')->default('{}');
            $table->jsonb('care_instructions')->default('[]');

            $table->timestamps();
            $table->softDeletes();
        });
        DB::statement('CREATE INDEX idx_variants_product_id ON product_variants (product_id) WHERE deleted_at IS NULL');
        DB::statement('CREATE INDEX idx_variants_option_values ON product_variants USING GIN (option_values)');
        DB::statement('CREATE INDEX idx_variants_deleted ON product_variants (deleted_at) WHERE deleted_at IS NOT NULL');
        DB::statement('CREATE INDEX idx_variants_status ON product_variants (status) WHERE deleted_at IS NULL');
        DB::statement('CREATE INDEX idx_variants_price ON product_variants (price) WHERE deleted_at IS NULL');
        DB::statement('CREATE UNIQUE INDEX unq_variants_sku_active ON product_variants (sku) WHERE deleted_at IS NULL');
    }

    public function down(): void
    {
        Schema::dropIfExists('product_variants');
        Schema::dropIfExists('products');
    }
};
