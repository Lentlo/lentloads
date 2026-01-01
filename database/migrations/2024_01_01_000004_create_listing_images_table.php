<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('listing_images', function (Blueprint $table) {
            $table->id();
            $table->foreignId('listing_id')->constrained()->onDelete('cascade');
            $table->string('path');
            $table->string('thumbnail')->nullable();
            $table->string('medium')->nullable();
            $table->string('original_name')->nullable();
            $table->integer('size')->nullable();
            $table->string('mime_type')->nullable();
            $table->integer('order')->default(0);
            $table->boolean('is_primary')->default(false);
            $table->timestamps();

            $table->index(['listing_id', 'order']);
            $table->index(['listing_id', 'is_primary']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('listing_images');
    }
};
