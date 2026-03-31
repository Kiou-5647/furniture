<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('categories', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('group_id')->nullable()->constrained('lookups')->onDelete('restrict');
            $table->string('product_type', 20)->nullable();
            $table->string('display_name');
            $table->string('slug', 64);
            $table->text('description')->nullable();
            $table->boolean('is_active')->default(true);
            $table->jsonb('metadata')->default('{}');
            $table->timestamps();
            $table->softDeletes();
        });
        DB::statement('CREATE UNIQUE INDEX unq_categories_slug_active ON categories (slug) WHERE deleted_at IS NULL');
        DB::statement('CREATE INDEX idx_categories_trgm ON categories USING GIN (display_name gin_trgm_ops)');
        DB::statement('CREATE INDEX idx_categories_active ON categories (is_active) WHERE is_active = true AND deleted_at IS NULL');
        DB::statement('CREATE INDEX idx_categories_deleted ON categories (deleted_at) WHERE deleted_at IS NOT NULL');

        Schema::create('category_room', function (Blueprint $table) {
            $table->foreignUuid('category_id')->constrained()->onDelete('cascade');
            $table->foreignUuid('room_id')->constrained('lookups')->onDelete('cascade');
            $table->primary(['category_id', 'room_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('category_room');
        Schema::dropIfExists('categories');
    }
};
