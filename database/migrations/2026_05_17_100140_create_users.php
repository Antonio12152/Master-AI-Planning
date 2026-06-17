<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('users', function (Blueprint $table) {
            $table->id();

            $table->string('email')->unique();
            $table->timestamp('email_verified_at')->nullable();
            $table->string('password');
            $table->rememberToken();

            $table->string('name');
            $table->string('avatar_url')->nullable();
            $table->text('bio')->nullable();
            $table->string('timezone')->default('UTC');

            $table->boolean('is_active')->default(true);
            $table->boolean('is_verified')->default(false);
            $table->boolean('is_admin')->default(false);

            $table->timestamps();
            $table->timestamp('last_login_at')->nullable();

            $table->index('email');
            $table->index('created_at');
            $table->index('is_active');
            $table->index('is_admin');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('users');
    }
};