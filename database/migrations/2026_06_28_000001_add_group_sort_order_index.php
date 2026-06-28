<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * 
     * Add composite index on (group_id, sort_order) for efficient group-scoped ordering queries
     */
    public function up(): void
    {
        Schema::table('ideas', function (Blueprint $table) {
            $table->index(['group_id', 'sort_order']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('ideas', function (Blueprint $table) {
            $table->dropIndex(['group_id', 'sort_order']);
        });
    }
};
