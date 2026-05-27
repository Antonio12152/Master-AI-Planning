<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ideas', function (Blueprint $table) {
            $table->id();

            $table->foreignId('plan_id')->constrained()->onDelete('cascade');
            $table->foreignId('group_id')->nullable()->constrained('idea_groups')->onDelete('set null');

            $table->string('text');
            $table->text('description')->nullable();

            $table->string('status')->default('new')->index(); // new, in_progress, completed, rejected

            $table->integer('priority')->default(0); // 0=low, 1=medium, 2=high, 3=critical

            $table->json('tags')->nullable();

            $table->integer('sort_order')->default(0);

            $table->timestamp('completed_at')->nullable();

            $table->timestamps();

            $table->index('plan_id');
            $table->index('group_id');
            $table->index(['plan_id', 'group_id']);
            $table->index('created_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ideas');
    }
};