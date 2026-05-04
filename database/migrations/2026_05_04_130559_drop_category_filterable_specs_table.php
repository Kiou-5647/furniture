<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::dropIfExists('category_filterable_specs');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::create('category_filterable_specs', function (Blueprint $table) {
            $table->uuid('category_id');
            $table->uuid('namespace_id');

            $table->primary(['category_id', 'namespace_id']);

            $table->foreign('category_id')->references('id')->on('categories')->onDelete('cascade');
            $table->foreign('namespace_id')->references('id')->on('lookup_namespaces')->onDelete('cascade');
        });
    }
};
