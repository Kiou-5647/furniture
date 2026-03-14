<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $sql = file_get_contents(database_path('schemas/00_create_extensions.sql'));
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
        DB::statement('DROP EXTENSION IF EXISTS vector CASCADE');
        DB::statement('DROP EXTENSION IF EXISTS btree_gist CASCADE');
        DB::statement('DROP EXTENSION IF EXISTS pg_trgm CASCADE');
        DB::statement('DROP EXTENSION IF EXISTS "uuid-ossp" CASCADE');
        DB::statement('DROP EXTENSION IF EXISTS pgcrypto CASCADE');
    }
};
