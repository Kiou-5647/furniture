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
        DB::statement('CREATE EXTENSION IF NOT EXISTS pgcrypto CASCADE');
        DB::statement('CREATE EXTENSION IF NOT EXISTS "uuid-ossp" CASCADE');
        DB::statement('CREATE EXTENSION IF NOT EXISTS pg_trgm CASCADE');
        DB::statement('CREATE EXTENSION IF NOT EXISTS btree_gist CASCADE');
        DB::statement('CREATE EXTENSION IF NOT EXISTS vector CASCADE');
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
