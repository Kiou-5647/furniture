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
        Schema::create('settings', function (Blueprint $table) {
            $table->id();
            $table->string('namespace', 50);
            $table->string('key', 100);
            $table->jsonb('value')->nullable();
            $table->text('description')->nullable();
            $table->boolean('is_system')->default(false);
            $table->timestamps();

            $table->unique(['namespace', 'key']);
            $table->index('key');
        });
        DB::statement('CREATE INDEX idx_settings_system ON settings(is_system) WHERE is_system = true');

        Schema::create('positions', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->string('code', 50)->unique();
            $table->text('description')->nullable();
            $table->boolean('is_system')->default(false);
            $table->timestamps();
        });

        Schema::create('lookups', function (Blueprint $table) {
            $table->id();
            $table->string('namespace', 64)->nullable();
            $table->string('slug', 64);
            $table->string('display_name');
            $table->text('description')->nullable();
            $table->string('image_path')->nullable();
            $table->boolean('is_active')->default(true);
            $table->jsonb('metadata')->default('{}');
            $table->timestamps();
            $table->softDeletes();

            $table->index('namespace');
        });
        DB::statement('CREATE UNIQUE INDEX unq_lookups_slug_active ON lookups (namespace, slug) WHERE deleted_at IS NULL');
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
        Schema::dropIfExists('positions');
        Schema::dropIfExists('settings');
    }
};
