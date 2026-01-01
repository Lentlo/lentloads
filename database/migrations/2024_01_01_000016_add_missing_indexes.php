<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Adds missing indexes for better query performance.
     * Uses safe approach to avoid duplicate index errors.
     */
    public function up(): void
    {
        // Helper to check if index exists
        $hasIndex = function ($table, $indexName) {
            $indexes = DB::select("SHOW INDEX FROM `$table` WHERE Key_name = ?", [$indexName]);
            return count($indexes) > 0;
        };

        // Add index to messages.type for filtering by message type
        if (!$hasIndex('messages', 'messages_type_index')) {
            Schema::table('messages', function (Blueprint $table) {
                $table->index('type');
            });
        }

        // Add index to conversations.is_blocked for filtering blocked conversations
        if (!$hasIndex('conversations', 'conversations_is_blocked_index')) {
            Schema::table('conversations', function (Blueprint $table) {
                $table->index('is_blocked');
            });
        }

        // Add index to user_packages.starts_at for date range queries
        if (!$hasIndex('user_packages', 'user_packages_starts_at_index')) {
            Schema::table('user_packages', function (Blueprint $table) {
                $table->index('starts_at');
            });
        }

        // Note: listings.slug and categories.slug already have indexes from unique constraints
        // Note: reports already has composite index from morphs() definition
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $hasIndex = function ($table, $indexName) {
            $indexes = DB::select("SHOW INDEX FROM `$table` WHERE Key_name = ?", [$indexName]);
            return count($indexes) > 0;
        };

        if ($hasIndex('messages', 'messages_type_index')) {
            Schema::table('messages', function (Blueprint $table) {
                $table->dropIndex(['type']);
            });
        }

        if ($hasIndex('conversations', 'conversations_is_blocked_index')) {
            Schema::table('conversations', function (Blueprint $table) {
                $table->dropIndex(['is_blocked']);
            });
        }

        if ($hasIndex('user_packages', 'user_packages_starts_at_index')) {
            Schema::table('user_packages', function (Blueprint $table) {
                $table->dropIndex(['starts_at']);
            });
        }
    }
};
