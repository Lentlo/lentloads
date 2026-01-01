<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('conversations', function (Blueprint $table) {
            $table->id();
            $table->string('uuid')->unique();
            $table->foreignId('listing_id')->constrained()->onDelete('cascade');
            $table->foreignId('buyer_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('seller_id')->constrained('users')->onDelete('cascade');
            $table->timestamp('buyer_last_read_at')->nullable();
            $table->timestamp('seller_last_read_at')->nullable();
            $table->boolean('buyer_deleted')->default(false);
            $table->boolean('seller_deleted')->default(false);
            $table->boolean('is_blocked')->default(false);
            $table->foreignId('blocked_by')->nullable()->constrained('users');
            $table->timestamps();

            $table->unique(['listing_id', 'buyer_id', 'seller_id']);
            $table->index('buyer_id');
            $table->index('seller_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('conversations');
    }
};
