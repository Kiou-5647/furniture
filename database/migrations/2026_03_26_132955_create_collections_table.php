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
        Schema::create('collections', function (Blueprint $table) {
            $table->id();
            $table->string('display_name', 255);
            $table->string('slug', 64);
            $table->string('image_path', 500)->nullable();
            $table->boolean('is_active')->default(true);
            $table->boolean('is_featured')->default(false);
            $table->text('description')->nullable();
            $table->jsonb('metadata')->default('{}');
            $table->timestamps();
            $table->softDeletes();
        });

        DB::statement('CREATE UNIQUE INDEX unq_collections_slug_active ON collections (slug) WHERE deleted_at IS NULL');
        DB::statement('CREATE INDEX idx_collections_display_name_trgm ON collections USING GIN (display_name gin_trgm_ops)');
        DB::statement('CREATE INDEX idx_collections_active ON collections (is_active) WHERE is_active = true AND deleted_at IS NOT NULL');
        DB::statement('CREATE INDEX idx_collections_featured ON collections (is_featured) WHERE is_featured = true');
        DB::statement('CREATE INDEX idx_collections_deleted ON collections (deleted_at) WHERE deleted_at IS NOT NULL');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('collections');
    }
};
