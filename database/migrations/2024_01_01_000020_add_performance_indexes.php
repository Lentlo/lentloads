<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Adds performance indexes for common query patterns.
     */
    public function up(): void
    {
        // Helper to check if index exists
        $hasIndex = function ($table, $indexName) {
            $indexes = DB::select("SHOW INDEX FROM `$table` WHERE Key_name = ?", [$indexName]);
            return count($indexes) > 0;
        };

        // Messages: Composite index for unread count queries (used in conversation list)
        // Query: WHERE conversation_id = ? AND is_read = false AND sender_id != ?
        if (!$hasIndex('messages', 'messages_unread_count_index')) {
            Schema::table('messages', function (Blueprint $table) {
                $table->index(['conversation_id', 'is_read', 'sender_id'], 'messages_unread_count_index');
            });
        }

        // Reviews: Composite index for approved reviews by user
        // Query: WHERE reviewed_id = ? AND status = 'approved'
        if (!$hasIndex('reviews', 'reviews_user_status_index')) {
            Schema::table('reviews', function (Blueprint $table) {
                $table->index(['reviewed_id', 'status'], 'reviews_user_status_index');
            });
        }

        // Reviews: Index on status for filtering
        if (!$hasIndex('reviews', 'reviews_status_index')) {
            Schema::table('reviews', function (Blueprint $table) {
                $table->index('status', 'reviews_status_index');
            });
        }

        // Conversations: Index for buyer conversations query
        // Query: WHERE buyer_id = ? AND buyer_deleted = false
        if (!$hasIndex('conversations', 'conversations_buyer_index')) {
            Schema::table('conversations', function (Blueprint $table) {
                $table->index(['buyer_id', 'buyer_deleted'], 'conversations_buyer_index');
            });
        }

        // Conversations: Index for seller conversations query
        // Query: WHERE seller_id = ? AND seller_deleted = false
        if (!$hasIndex('conversations', 'conversations_seller_index')) {
            Schema::table('conversations', function (Blueprint $table) {
                $table->index(['seller_id', 'seller_deleted'], 'conversations_seller_index');
            });
        }

        // Listings: Index on expires_at for not expired queries
        if (!$hasIndex('listings', 'listings_expires_at_index')) {
            Schema::table('listings', function (Blueprint $table) {
                $table->index('expires_at', 'listings_expires_at_index');
            });
        }
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

        if ($hasIndex('messages', 'messages_unread_count_index')) {
            Schema::table('messages', function (Blueprint $table) {
                $table->dropIndex('messages_unread_count_index');
            });
        }

        if ($hasIndex('reviews', 'reviews_user_status_index')) {
            Schema::table('reviews', function (Blueprint $table) {
                $table->dropIndex('reviews_user_status_index');
            });
        }

        if ($hasIndex('reviews', 'reviews_status_index')) {
            Schema::table('reviews', function (Blueprint $table) {
                $table->dropIndex('reviews_status_index');
            });
        }

        if ($hasIndex('conversations', 'conversations_buyer_index')) {
            Schema::table('conversations', function (Blueprint $table) {
                $table->dropIndex('conversations_buyer_index');
            });
        }

        if ($hasIndex('conversations', 'conversations_seller_index')) {
            Schema::table('conversations', function (Blueprint $table) {
                $table->dropIndex('conversations_seller_index');
            });
        }

        if ($hasIndex('listings', 'listings_expires_at_index')) {
            Schema::table('listings', function (Blueprint $table) {
                $table->dropIndex('listings_expires_at_index');
            });
        }
    }
};
