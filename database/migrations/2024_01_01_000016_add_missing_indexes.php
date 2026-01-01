<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Adds missing indexes for better query performance.
     */
    public function up(): void
    {
        // Add index to listings.slug for faster slug lookups
        Schema::table('listings', function (Blueprint $table) {
            $table->index('slug');
        });

        // Add index to categories.slug for faster slug lookups
        Schema::table('categories', function (Blueprint $table) {
            $table->index('slug');
        });

        // Add index to messages.type for filtering by message type
        Schema::table('messages', function (Blueprint $table) {
            $table->index('type');
        });

        // Add index to conversations.is_blocked for filtering blocked conversations
        Schema::table('conversations', function (Blueprint $table) {
            $table->index('is_blocked');
        });

        // Add index to user_packages.starts_at for date range queries
        Schema::table('user_packages', function (Blueprint $table) {
            $table->index('starts_at');
        });

        // Composite index for reports - already exists from morphs() definition
        // Skipping: $table->index(['reportable_type', 'reportable_id']);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('listings', function (Blueprint $table) {
            $table->dropIndex(['slug']);
        });

        Schema::table('categories', function (Blueprint $table) {
            $table->dropIndex(['slug']);
        });

        Schema::table('messages', function (Blueprint $table) {
            $table->dropIndex(['type']);
        });

        Schema::table('conversations', function (Blueprint $table) {
            $table->dropIndex(['is_blocked']);
        });

        Schema::table('user_packages', function (Blueprint $table) {
            $table->dropIndex(['starts_at']);
        });

        // Reports index not added by this migration
    }
};
