<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('saved_searches', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('name');
            $table->string('query')->nullable();
            $table->foreignId('category_id')->nullable()->constrained();
            $table->string('city')->nullable();
            $table->decimal('min_price', 12, 2)->nullable();
            $table->decimal('max_price', 12, 2)->nullable();
            $table->json('filters')->nullable();
            $table->boolean('notify_email')->default(false);
            $table->boolean('notify_push')->default(true);
            $table->enum('notify_frequency', ['instant', 'daily', 'weekly'])->default('daily');
            $table->timestamp('last_notified_at')->nullable();
            $table->timestamps();

            $table->index('user_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('saved_searches');
    }
};
