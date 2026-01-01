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
            $table->string('name');
            $table->string('email')->unique();
            $table->timestamp('email_verified_at')->nullable();
            $table->string('password');
            $table->string('phone')->nullable();
            $table->boolean('phone_verified')->default(false);
            $table->string('avatar')->nullable();
            $table->text('bio')->nullable();
            $table->string('city')->nullable();
            $table->string('state')->nullable();
            $table->string('country')->default('IN');
            $table->decimal('latitude', 10, 8)->nullable();
            $table->decimal('longitude', 11, 8)->nullable();
            $table->enum('role', ['user', 'admin', 'moderator'])->default('user');
            $table->enum('status', ['active', 'suspended', 'banned'])->default('active');
            $table->boolean('is_verified_seller')->default(false);
            $table->decimal('rating', 3, 2)->default(0);
            $table->integer('total_reviews')->default(0);
            $table->integer('total_listings')->default(0);
            $table->integer('successful_sales')->default(0);
            $table->timestamp('last_active_at')->nullable();
            $table->json('notification_preferences')->nullable();
            $table->rememberToken();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['city', 'state', 'country']);
            $table->index('status');
            $table->index('role');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('users');
    }
};
