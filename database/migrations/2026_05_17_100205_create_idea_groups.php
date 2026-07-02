<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('idea_groups', function (Blueprint $table) {
            $table->id();

            $table->foreignId('plan_id')->constrained()->onDelete('cascade');

            $table->string('name');
            $table->text('description')->nullable();

            $table->integer('sort_order')->default(0);
            $table->integer('idea_count')->default(0);

            $table->string('color')->nullable();

            $table->timestamps();

            $table->index('plan_id');
            $table->index(['plan_id', 'sort_order']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('idea_groups');
    }
};