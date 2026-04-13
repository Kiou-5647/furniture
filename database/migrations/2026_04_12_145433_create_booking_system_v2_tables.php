<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('design_services', function (Blueprint $table) {
            $table->renameColumn('estimated_minutes', 'estimated_hours');
            $table->integer('deadline_days')->nullable()->after('estimated_hours');
        });

        Schema::table('bookings', function (Blueprint $table) {
            $table->foreignUuid('deposit_invoice_id')->nullable()->after('accepted_by')->constrained('invoices')->onDelete('set null');
            $table->foreignUuid('final_invoice_id')->nullable()->after('deposit_invoice_id')->constrained('invoices')->onDelete('set null');
        });

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

        Schema::create('designer_date_availabilities', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('designer_id')->constrained('designers')->onDelete('cascade');
            $table->date('date');
            $table->tinyInteger('start_hour')->comment('0-23');
            $table->tinyInteger('end_hour')->comment('0-24 (exclusive)');
            $table->boolean('is_available')->default(true);
            $table->timestamps();

            $table->unique(['designer_id', 'date']);
        });
        DB::statement('CREATE INDEX idx_designer_date_availabilities_designer_date ON designer_date_availabilities (designer_id, date)');
        DB::statement('CREATE INDEX idx_designer_date_availabilities_date ON designer_date_availabilities (date) WHERE is_available = true');

        Schema::create('booking_sessions', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('booking_id')->constrained('bookings')->onDelete('cascade');
            $table->date('date');
            $table->tinyInteger('start_hour')->comment('0-23');
            $table->tinyInteger('end_hour')->comment('0-24 (exclusive)');
            $table->timestamps();

            $table->unique(['booking_id', 'date', 'start_hour']);
        });
        DB::statement('CREATE INDEX idx_booking_sessions_designer_date ON booking_sessions (date, start_hour)');
    }

    public function down(): void
    {
        Schema::dropIfExists('booking_sessions');
        Schema::dropIfExists('designer_date_availabilities');
        Schema::dropIfExists('designer_availability_slots');

        Schema::table('bookings', function (Blueprint $table) {
            $table->dropForeign(['deposit_invoice_id']);
            $table->dropForeign(['final_invoice_id']);
            $table->dropColumn(['deposit_invoice_id', 'final_invoice_id']);
        });

        Schema::table('design_services', function (Blueprint $table) {
            $table->renameColumn('estimated_hours', 'estimated_minutes');
            $table->dropColumn('deadline_days');
        });
    }
};
