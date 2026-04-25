<?php

use App\Models\Auth\User;
use App\Models\Hr\Department;
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
        Schema::create('departments', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('manager_id')->nullable();
            $table->string('name', 100)->unique();
            $table->string('code', 50)->unique();
            $table->text('description')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('employees', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignIdFor(User::class, 'user_id')->unique()->constrained()->onDelete('cascade');
            $table->foreignIdFor(Department::class, 'department_id')->nullable()->constrained()->onDelete('set null');
            $table->string('full_name');
            $table->string('phone', 20)->nullable();
            $table->date('hire_date')->nullable();
            $table->date('termination_date')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
        DB::statement('ALTER TABLE departments ADD CONSTRAINT fk_dept_manager FOREIGN KEY (manager_id) REFERENCES employees(id) DEFERRABLE INITIALLY DEFERRED');

        Schema::create('customers', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignIdFor(User::class, 'user_id')->unique()->constrained()->onDelete('cascade');
            $table->string('full_name')->nullable();
            $table->string('phone', 20)->nullable();
            $table->decimal('total_spent', 15, 2)->default(0);
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('customer_addresses', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('customer_id');
            $table->foreign('customer_id')->references('id')->on('customers')->onDelete('cascade');
            $table->foreign('province_code')->references('province_code')->on('provinces')->onDelete('set null');
            $table->foreign('ward_code')->references('ward_code')->on('wards')->onDelete('set null');
            $table->string('type', 20)->default('shipping');
            $table->text('delivery_instructions')->nullable();
            $table->string('province_code', 2)->nullable();
            $table->string('ward_code', 5)->nullable();
            $table->string('province_name')->nullable();
            $table->string('ward_name')->nullable();
            $table->jsonb('address_data')->default('{}');
            $table->boolean('is_default')->default(false);
            $table->timestamps();
            $table->softDeletes();
        });
        DB::statement("ALTER TABLE customer_addresses ADD CONSTRAINT chk_address_type CHECK (type IN ('shipping', 'billing', 'both'))");
        DB::statement('CREATE INDEX idx_addresses_customer_id ON customer_addresses(customer_id)');
        DB::statement('CREATE INDEX idx_customer_addresses_default ON customer_addresses(is_default) WHERE is_default = true');
        DB::statement('CREATE UNIQUE INDEX uq_customer_default_address ON customer_addresses(customer_id) WHERE is_default = true AND deleted_at IS NULL');

        Schema::create('vendors', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('name');
            $table->string('contact_name')->nullable();
            $table->string('email')->nullable();
            $table->string('phone', 20)->nullable();
            $table->string('website')->nullable();
            $table->string('province_code', 2)->nullable();
            $table->string('ward_code', 5)->nullable();
            $table->jsonb('address_data')->default('{}');
            $table->text('bank_name')->nullable();
            $table->string('bank_account_number', 100)->nullable();
            $table->string('bank_account_holder')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamp('verified_at')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('province_code')->references('province_code')->on('provinces')->onDelete('set null');
            $table->foreign('ward_code')->references('ward_code')->on('wards')->onDelete('set null');
        });

        DB::statement('CREATE INDEX idx_vendors_active_clean ON vendors(is_active) WHERE is_active = true AND deleted_at IS NULL');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('vendors');
        Schema::dropIfExists('customer_addresses');
        Schema::dropIfExists('customers');
        Schema::dropIfExists('employees');
        Schema::dropIfExists('departments');
    }
};
