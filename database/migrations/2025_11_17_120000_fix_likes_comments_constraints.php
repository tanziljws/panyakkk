<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Fix likes table - remove unique constraint that blocks guest likes
        if (Schema::hasTable('likes')) {
            $driver = Schema::getConnection()->getDriverName();
            
            // Check if unique constraint exists and drop it
            try {
                if ($driver === 'mysql') {
                    // Check for unique index
                    $indexes = DB::select("SHOW INDEXES FROM `likes` WHERE Key_name = 'likes_user_id_galeri_id_unique'");
                    if (!empty($indexes)) {
                        DB::statement('ALTER TABLE `likes` DROP INDEX `likes_user_id_galeri_id_unique`');
                    }
                } elseif ($driver === 'pgsql') {
                    DB::statement('DROP INDEX IF EXISTS likes_user_id_galeri_id_unique');
                }
            } catch (\Exception $e) {
                // Index might not exist, continue
            }
            
            // Ensure user_id is nullable
            try {
                if ($driver === 'mysql') {
                    $columns = DB::select("SHOW COLUMNS FROM `likes` WHERE Field = 'user_id'");
                    if (!empty($columns) && strpos($columns[0]->Null ?? '', 'YES') === false) {
                        DB::statement('ALTER TABLE `likes` MODIFY `user_id` BIGINT UNSIGNED NULL');
                    }
                }
            } catch (\Exception $e) {
                // Column might already be nullable
            }
            
            // Ensure guest_token exists
            if (!Schema::hasColumn('likes', 'guest_token')) {
                Schema::table('likes', function (Blueprint $table) {
                    $table->uuid('guest_token')->nullable()->after('user_id');
                    $table->index(['guest_token', 'galeri_id']);
                });
            }
        }
        
        // Fix comments table - ensure user_id is nullable
        if (Schema::hasTable('comments')) {
            $driver = Schema::getConnection()->getDriverName();
            
            try {
                if ($driver === 'mysql') {
                    $columns = DB::select("SHOW COLUMNS FROM `comments` WHERE Field = 'user_id'");
                    if (!empty($columns) && strpos($columns[0]->Null ?? '', 'YES') === false) {
                        DB::statement('ALTER TABLE `comments` MODIFY `user_id` BIGINT UNSIGNED NULL');
                    }
                }
            } catch (\Exception $e) {
                // Column might already be nullable
            }
            
            // Ensure guest_name exists
            if (!Schema::hasColumn('comments', 'guest_name')) {
                Schema::table('comments', function (Blueprint $table) {
                    $table->string('guest_name')->nullable()->after('user_id');
                });
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // This is a fix migration, no need to reverse
    }
};

