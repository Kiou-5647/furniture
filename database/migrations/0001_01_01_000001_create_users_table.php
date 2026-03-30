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
        Schema::create('users', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('type', 20)->default('customer');
            $table->string('name');
            $table->string('email')->unique();
            $table->string('password');
            $table->boolean('is_active')->default(true);
            $table->boolean('is_verified')->default(false);
            $table->timestamp('email_verified_at')->nullable();

            $table->ipAddress('last_login_ip')->nullable();
            $table->timestamp('last_login_at')->nullable();
            $table->rememberToken();

            $table->text('two_factor_secret')->nullable();
            $table->text('two_factor_recovery_codes')->nullable();
            $table->timestamp('two_factor_confirmed_at')->nullable();

            $table->timestamps();
            $table->softDeletes();
        });
        DB::statement("ALTER TABLE users ADD CONSTRAINT chk_users_type CHECK (type IN ('employee', 'vendor', 'customer'))");
        DB::statement('CREATE INDEX idx_users_type ON users(type)');
        DB::statement('CREATE INDEX idx_users_active ON users(is_active) WHERE is_active = true AND deleted_at IS NULL');
        DB::statement('CREATE INDEX idx_users_deleted ON users(deleted_at) WHERE deleted_at IS NOT NULL');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('users');
    }
};
