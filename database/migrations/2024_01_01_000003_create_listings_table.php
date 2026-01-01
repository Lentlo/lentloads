<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('listings', function (Blueprint $table) {
            $table->id();
            $table->string('uuid')->unique();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('category_id')->constrained()->onDelete('cascade');
            $table->string('title');
            $table->string('slug')->unique();
            $table->text('description');
            $table->decimal('price', 12, 2);
            $table->enum('price_type', ['fixed', 'negotiable', 'free', 'contact'])->default('fixed');
            $table->enum('condition', ['new', 'like_new', 'good', 'fair', 'poor'])->nullable();
            $table->string('brand')->nullable();
            $table->string('model')->nullable();
            $table->integer('year')->nullable();

            // Location
            $table->string('address')->nullable();
            $table->string('city');
            $table->string('state')->nullable();
            $table->string('country')->default('IN');
            $table->string('postal_code')->nullable();
            $table->decimal('latitude', 10, 8)->nullable();
            $table->decimal('longitude', 11, 8)->nullable();

            // Status
            $table->enum('status', ['draft', 'pending', 'active', 'sold', 'expired', 'rejected'])->default('pending');
            $table->string('rejection_reason')->nullable();

            // Stats
            $table->integer('views_count')->default(0);
            $table->integer('favorites_count')->default(0);
            $table->integer('contacts_count')->default(0);

            // Features
            $table->boolean('is_featured')->default(false);
            $table->boolean('is_urgent')->default(false);
            $table->boolean('is_highlighted')->default(false);
            $table->timestamp('featured_until')->nullable();

            // Custom attributes (JSON for category-specific fields)
            $table->json('attributes')->nullable();

            $table->timestamp('published_at')->nullable();
            $table->timestamp('expires_at')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['user_id', 'status']);
            $table->index(['category_id', 'status']);
            $table->index(['city', 'state', 'country']);
            $table->index(['latitude', 'longitude']);
            $table->index('status');
            $table->index('price');
            $table->index('is_featured');
            $table->index('published_at');
            $table->fullText(['title', 'description']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('listings');
    }
};
