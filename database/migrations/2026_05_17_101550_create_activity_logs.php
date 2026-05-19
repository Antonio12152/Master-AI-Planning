<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('activity_logs', function (Blueprint $table) {
            $table->id();
            
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('plan_id')->nullable()->constrained()->onDelete('set null');
            
            $table->string('action')->index(); // created_plan, added_idea, moved_idea, etc
            $table->string('entity_type'); // plan, group, idea
            $table->unsignedBigInteger('entity_id')->nullable();
            
            $table->json('details')->default('{}'); 
            $table->json('changes')->default('{}'); // {from: "old_name", to: "new_name"}
            
            $table->timestamp('created_at')->useCurrent();
            
            $table->index('user_id');
            $table->index('plan_id');
            $table->index('action');
            $table->index('created_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('activity_logs');
    }
};