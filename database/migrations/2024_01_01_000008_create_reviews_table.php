<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('reviews', function (Blueprint $table) {
            $table->id();
            $table->foreignId('reviewer_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('reviewed_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('listing_id')->nullable()->constrained()->onDelete('set null');
            $table->tinyInteger('rating'); // 1-5
            $table->text('comment')->nullable();
            $table->enum('type', ['buyer', 'seller']); // Who is being reviewed
            $table->boolean('is_verified_purchase')->default(false);
            $table->enum('status', ['pending', 'approved', 'rejected'])->default('approved');
            $table->text('seller_response')->nullable();
            $table->timestamp('seller_responded_at')->nullable();
            $table->timestamps();

            $table->unique(['reviewer_id', 'listing_id', 'type']);
            $table->index('reviewed_id');
            $table->index('rating');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('reviews');
    }
};
