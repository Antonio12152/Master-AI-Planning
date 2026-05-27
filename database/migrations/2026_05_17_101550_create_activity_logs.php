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
            
            $table->string('action')->index();
            $table->string('entity_type');
            $table->unsignedBigInteger('entity_id')->nullable();
            
            $table->json('details')->default('{}');
            $table->json('changes')->default('{}');
            
            $table->timestamp('created_at')->useCurrent();
            
            $table->index('user_id');
            $table->index('plan_id');
            $table->index('created_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('activity_logs');
    }
};