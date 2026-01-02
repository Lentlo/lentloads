<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Add FULLTEXT index for better search
        DB::statement('ALTER TABLE listings ADD FULLTEXT INDEX listings_fulltext_search (title, description)');

        // Add search_terms column for phonetic matching
        Schema::table('listings', function (Blueprint $table) {
            $table->string('search_terms', 500)->nullable()->after('description');
        });
    }

    public function down(): void
    {
        DB::statement('ALTER TABLE listings DROP INDEX listings_fulltext_search');

        Schema::table('listings', function (Blueprint $table) {
            $table->dropColumn('search_terms');
        });
    }
};
