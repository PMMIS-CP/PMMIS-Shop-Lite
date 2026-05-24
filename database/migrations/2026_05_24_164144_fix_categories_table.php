<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    public function up(): void
    {
        // Add foreign key constraint (CRIT-004)
        Schema::table('categories', function (Blueprint $table) {
            $table->foreign('parent_id')
                ->references('id')
                ->on('categories')
                ->onDelete('cascade');
        });

        // Add virtual generated columns for slug (CRIT-005)
        // MySQL / MariaDB syntax
        DB::statement('ALTER TABLE categories ADD COLUMN slug_fa VARCHAR(255) GENERATED ALWAYS AS (JSON_UNQUOTE(slug->>"$.fa")) STORED');
        DB::statement('ALTER TABLE categories ADD COLUMN slug_en VARCHAR(255) GENERATED ALWAYS AS (JSON_UNQUOTE(slug->>"$.en")) STORED');
        
        // Add indexes and unique constraints
        Schema::table('categories', function (Blueprint $table) {
            $table->index('slug_fa');
            $table->index('slug_en');
            $table->unique('slug_fa');
            $table->unique('slug_en');
        });
    }

    public function down(): void
    {
        Schema::table('categories', function (Blueprint $table) {
            $table->dropForeign(['parent_id']);
            $table->dropIndex(['slug_fa']);
            $table->dropIndex(['slug_en']);
            $table->dropUnique(['slug_fa']);
            $table->dropUnique(['slug_en']);
        });
        
        DB::statement('ALTER TABLE categories DROP COLUMN slug_fa');
        DB::statement('ALTER TABLE categories DROP COLUMN slug_en');
    }
};