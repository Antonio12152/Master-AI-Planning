<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('plans', function (Blueprint $table) {
            $table->id();

            $table->foreignId('user_id')->constrained()->onDelete('cascade');

            $table->string('name');
            $table->text('description')->nullable();

            $table->string('status')->default('active')->index(); // active, inactive, archived

            $table->string('color')->nullable();
            $table->string('icon')->nullable();

            $table->integer('idea_count')->default(0);
            $table->integer('group_count')->default(0);
            $table->integer('member_count')->default(0);

            $table->boolean('is_public')->default(false);

            $table->timestamps();
            $table->timestamp('archived_at')->nullable();

            $table->index('user_id');
            $table->index('created_at');
            $table->index(['user_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('plans');
    }
};