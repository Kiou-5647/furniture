<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('employees', function (Blueprint $table) {
            $table->renameColumn('location_id', 'store_location_id');
        });

        Schema::table('employees', function (Blueprint $table) {
            $table->uuid('warehouse_location_id')->nullable()->after('store_location_id')->constrained('locations');
        });

        // Data Migration: Move employees linked to 'warehouse' type locations
        $warehouseEmployees = DB::table('employees')
            ->join('locations', 'employees.store_location_id', '=', 'locations.id')
            ->where('locations.type', 'warehouse')
            ->select('employees.id', 'employees.store_location_id')
            ->get();

        foreach ($warehouseEmployees as $emp) {
            DB::table('employees')->where('id', $emp->id)->update([
                'warehouse_location_id' => $emp->store_location_id,
                'store_location_id' => null,
            ]);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Reverse data migration
        $storeEmployees = DB::table('employees')
            ->whereNotNull('warehouse_location_id')
            ->select('id', 'warehouse_location_id')
            ->get();

        foreach ($storeEmployees as $emp) {
            DB::table('employees')->where('id', $emp->id)->update([
                'store_location_id' => $emp->warehouse_location_id,
                'warehouse_location_id' => null,
            ]);
        }

        Schema::table('employees', function (Blueprint $table) {
            $table->dropColumn('warehouse_location_id');
            $table->renameColumn('store_location_id', 'location_id');
        });
    }
};
