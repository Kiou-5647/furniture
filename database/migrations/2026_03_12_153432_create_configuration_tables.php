<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $sql = file_get_contents(database_path('schemas/02_create_configuration_tables.sql'));
        foreach (array_filter(array_map('trim', explode(';', $sql))) as $statement) {
            if (! empty($statement)) {
                DB::statement($statement);
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('password_reset_tokens');
        Schema::dropIfExists('metafields');
        Schema::dropIfExists('lookups');
        Schema::dropIfExists('positions');
        Schema::dropIfExists('settings');
    }
};
