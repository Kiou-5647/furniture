<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('design_services', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('name');
            $table->string('type', 50)->comment('consultation, custom_build');
            $table->boolean('is_schedule_blocking')->default(true);
            $table->decimal('base_price', 15, 2)->default(0);
            $table->integer('deposit_percentage')->default(0);
            $table->integer('estimated_minutes')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('designers', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('user_id')->constrained('users')->onDelete('restrict');
            $table->foreignUuid('employee_id')->nullable()->constrained('employees')->onDelete('set null');
            $table->foreignUuid('vendor_user_id')->nullable()->constrained('vendor_users')->onDelete('set null');
            $table->text('bio')->nullable();
            $table->string('portfolio_url')->nullable();
            $table->decimal('hourly_rate', 15, 2)->nullable();
            $table->boolean('auto_confirm_bookings')->default(false);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->softDeletes();
        });

        DB::statement("CREATE INDEX idx_designers_user_id ON designers (user_id)");
        DB::statement("CREATE INDEX idx_designers_active ON designers (is_active) WHERE is_active = true AND deleted_at IS NULL");

        Schema::create('designer_availabilities', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('designer_id')->constrained('designers')->onDelete('cascade');
            $table->tinyInteger('day_of_week')->comment('0-6 (Sun-Sat)');
            $table->time('start_time');
            $table->time('end_time');
            $table->timestamps();
        });
        
        DB::statement("CREATE INDEX idx_designer_availabilities_designer_id ON designer_availabilities (designer_id)");

        Schema::create('bookings', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('customer_id')->constrained('users')->onDelete('restrict');
            $table->foreignUuid('designer_id')->constrained('designers')->onDelete('restrict');
            $table->foreignUuid('service_id')->constrained('design_services')->onDelete('restrict');
            $table->timestamp('start_at')->nullable();
            $table->timestamp('end_at')->nullable();
            $table->timestamp('deadline_at')->nullable();
            $table->string('status', 50)->default('pending_deposit');
            $table->foreignUuid('accepted_by')->nullable()->constrained('employees')->onDelete('set null');
            $table->timestamps();
            $table->softDeletes();
        });

        DB::statement("CREATE INDEX idx_bookings_customer_id ON bookings (customer_id)");
        DB::statement("CREATE INDEX idx_bookings_designer_id ON bookings (designer_id)");
        DB::statement("CREATE INDEX idx_bookings_status ON bookings (status)");
        DB::statement("CREATE INDEX idx_bookings_start_at ON bookings (start_at) WHERE start_at IS NOT NULL");
        DB::statement("CREATE INDEX idx_bookings_deadline_at ON bookings (deadline_at) WHERE deadline_at IS NOT NULL");
    }

    public function down(): void
    {
        Schema::dropIfExists('bookings');
        Schema::dropIfExists('designer_availabilities');
        Schema::dropIfExists('designers');
        Schema::dropIfExists('design_services');
    }
};
