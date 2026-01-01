<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasColumn('listings', 'locality')) {
            Schema::table('listings', function (Blueprint $table) {
                $table->string('locality')->nullable()->after('address');
                $table->index('locality');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('listings', 'locality')) {
            Schema::table('listings', function (Blueprint $table) {
                $table->dropIndex(['locality']);
                $table->dropColumn('locality');
            });
        }
    }
};
