<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('designers', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('user_id')->constrained('users')->onDelete('restrict');
            $table->foreignUuid('employee_id')->nullable()->constrained('employees')->onDelete('set null');
            $table->string('full_name')->nullable()->after('user_id');
            $table->string('phone', 20)->nullable()->after('full_name');
            $table->text('bio')->nullable();
            $table->string('portfolio_url')->nullable();
            $table->decimal('hourly_rate', 15, 2)->nullable();
            $table->boolean('auto_confirm_bookings')->default(false);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->softDeletes();
        });

        DB::statement('CREATE INDEX idx_designers_user_id ON designers (user_id)');
        DB::statement('CREATE INDEX idx_designers_active ON designers (is_active) WHERE is_active = true AND deleted_at IS NULL');

        Schema::create('bookings', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('customer_id')->constrained('users')->onDelete('restrict');
            $table->foreignUuid('designer_id')->constrained('designers')->onDelete('restrict');
            $table->dateTime('start_at')->nullable();
            $table->dateTime('end_at')->nullable();
            $table->string('status', 50)->default('pending_deposit');
            $table->decimal('total_price', 15, 2)->default(0);
            $table->text('notes')->nullable();
            $table->string('customer_name')->nullable();
            $table->string('customer_email')->nullable();
            $table->string('customer_phone')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        DB::statement('CREATE INDEX idx_bookings_customer_id ON bookings (customer_id)');
        DB::statement('CREATE INDEX idx_bookings_designer_id ON bookings (designer_id)');
        DB::statement('CREATE INDEX idx_bookings_status ON bookings (status)');
        DB::statement('CREATE INDEX idx_bookings_start_at ON bookings (start_at) WHERE start_at IS NOT NULL');

        Schema::create('designer_availability_slots', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('designer_id')->constrained('designers')->onDelete('cascade');
            $table->tinyInteger('day_of_week')->comment('0-6 (Sun-Sat)');
            $table->tinyInteger('hour')->comment('0-23');
            $table->boolean('is_available')->default(false);
            $table->timestamps();

            $table->unique(['designer_id', 'day_of_week', 'hour']);
        });
        DB::statement('CREATE INDEX idx_designer_availability_slots_designer ON designer_availability_slots (designer_id)');
    }

    public function down(): void
    {
        Schema::dropIfExists('designer_availability_slots');
        Schema::dropIfExists('bookings');
        Schema::dropIfExists('designers');
    }
};
