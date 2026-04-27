<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('categories', function (Blueprint $table) {
            $table->dropColumn('filterable_specs');
        });

        Schema::create('category_filterable_specs', function (Blueprint $table) {
            $table->uuid('category_id');
            $table->uuid('namespace_id');

            $table->foreign('category_id')
                ->references('id')->on('categories')
                ->cascadeOnDelete();

            $table->foreign('namespace_id')
                ->references('id')->on('lookup_namespaces')
                ->cascadeOnDelete();

            $table->primary(['category_id', 'namespace_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('category_filterable_specs');

        Schema::table('categories', function (Blueprint $table) {
            $table->json('filterable_specs')->nullable();
        });
    }
};
