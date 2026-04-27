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
        Schema::create('lookup_namespaces', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('slug', 64)->unique();
            $table->string('display_name', 255);
            $table->text('description')->nullable();
            $table->boolean('for_variants')->default(false);
            $table->boolean('is_active')->default(true);
            $table->boolean('is_system')->default(false);
            $table->timestamps();
        });
        DB::statement('CREATE INDEX idx_lookup_namespaces_slug ON lookup_namespaces (slug)');
        DB::statement('CREATE INDEX idx_lookup_namespaces_trgm ON lookup_namespaces USING GIN (display_name gin_trgm_ops)');
        DB::statement('CREATE INDEX idx_lookup_namespaces_variants ON lookup_namespaces (for_variants) WHERE for_variants = true');
        DB::statement('CREATE INDEX idx_lookup_namespaces_active ON lookup_namespaces (is_active) WHERE is_active = true');
        DB::statement('CREATE INDEX idx_lookup_namespaces_system ON lookup_namespaces (is_system) WHERE is_system = true');

        Schema::create('lookups', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('namespace_id')->nullable()->constrained('lookup_namespaces')->nullOnDelete();
            $table->string('slug', 64);
            $table->string('display_name');
            $table->text('description')->nullable();
            $table->boolean('is_active')->default(true);
            $table->jsonb('metadata')->default('{}');
            $table->timestamps();
            $table->softDeletes();

            $table->index('namespace_id');
        });
        DB::statement('CREATE UNIQUE INDEX unq_lookups_slug_active ON lookups (namespace_id, slug) WHERE deleted_at IS NULL');
        DB::statement('CREATE INDEX idx_lookups_trgm ON lookups USING GIN (display_name gin_trgm_ops)');
        DB::statement('CREATE INDEX idx_lookups_deleted ON lookups(deleted_at) WHERE deleted_at IS NOT NULL');

        Schema::create('password_reset_tokens', function (Blueprint $table) {
            $table->string('email')->primary();
            $table->string('token');
            $table->timestamp('created_at')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('password_reset_tokens');
        Schema::dropIfExists('lookups');
        Schema::dropIfExists('lookup_namespaces');
        Schema::dropIfExists('positions');
    }
};
