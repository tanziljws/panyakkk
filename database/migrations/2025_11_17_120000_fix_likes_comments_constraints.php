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
        $driver = Schema::getConnection()->getDriverName();
        
        // Fix likes table - remove unique constraint and fix foreign key
        if (Schema::hasTable('likes')) {
            try {
                if ($driver === 'mysql') {
                    // Drop foreign key constraint on user_id if it exists (to allow NULL)
                    $foreignKeys = DB::select("
                        SELECT CONSTRAINT_NAME 
                        FROM information_schema.KEY_COLUMN_USAGE 
                        WHERE TABLE_SCHEMA = DATABASE() 
                        AND TABLE_NAME = 'likes' 
                        AND COLUMN_NAME = 'user_id' 
                        AND REFERENCED_TABLE_NAME IS NOT NULL
                    ");
                    
                    foreach ($foreignKeys as $fk) {
                        try {
                            DB::statement("ALTER TABLE `likes` DROP FOREIGN KEY `{$fk->CONSTRAINT_NAME}`");
                        } catch (\Exception $e) {
                            // Foreign key might not exist or already dropped
                        }
                    }
                    
                    // Drop unique index if exists
                    $indexes = DB::select("SHOW INDEXES FROM `likes`");
                    foreach ($indexes as $index) {
                        if (strpos($index->Key_name, 'unique') !== false || 
                            ($index->Column_name === 'user_id' && $index->Non_unique == 0)) {
                            try {
                                DB::statement("ALTER TABLE `likes` DROP INDEX `{$index->Key_name}`");
                            } catch (\Exception $e) {
                                // Index might not exist
                            }
                        }
                    }
                    
                    // Ensure user_id is nullable
                    DB::statement('ALTER TABLE `likes` MODIFY `user_id` BIGINT UNSIGNED NULL');
                    
                    // Re-add foreign key but allow NULL
                    try {
                        DB::statement('ALTER TABLE `likes` ADD CONSTRAINT `likes_user_id_foreign` 
                            FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE');
                    } catch (\Exception $e) {
                        // Foreign key might already exist
                    }
                }
            } catch (\Exception $e) {
                \Log::warning('Error fixing likes table: ' . $e->getMessage());
            }
            
            // Ensure guest_token exists
            if (!Schema::hasColumn('likes', 'guest_token')) {
                try {
                    Schema::table('likes', function (Blueprint $table) {
                        $table->uuid('guest_token')->nullable()->after('user_id');
                        $table->index(['guest_token', 'galeri_id']);
                    });
                } catch (\Exception $e) {
                    \Log::warning('Error adding guest_token to likes: ' . $e->getMessage());
                }
            }
        }
        
        // Fix comments table - ensure user_id is nullable and fix foreign key
        if (Schema::hasTable('comments')) {
            try {
                if ($driver === 'mysql') {
                    // Drop foreign key constraint on user_id if it exists (to allow NULL)
                    $foreignKeys = DB::select("
                        SELECT CONSTRAINT_NAME 
                        FROM information_schema.KEY_COLUMN_USAGE 
                        WHERE TABLE_SCHEMA = DATABASE() 
                        AND TABLE_NAME = 'comments' 
                        AND COLUMN_NAME = 'user_id' 
                        AND REFERENCED_TABLE_NAME IS NOT NULL
                    ");
                    
                    foreach ($foreignKeys as $fk) {
                        try {
                            DB::statement("ALTER TABLE `comments` DROP FOREIGN KEY `{$fk->CONSTRAINT_NAME}`");
                        } catch (\Exception $e) {
                            // Foreign key might not exist or already dropped
                        }
                    }
                    
                    // Ensure user_id is nullable
                    DB::statement('ALTER TABLE `comments` MODIFY `user_id` BIGINT UNSIGNED NULL');
                    
                    // Re-add foreign key but allow NULL
                    try {
                        DB::statement('ALTER TABLE `comments` ADD CONSTRAINT `comments_user_id_foreign` 
                            FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE');
                    } catch (\Exception $e) {
                        // Foreign key might already exist
                    }
                }
            } catch (\Exception $e) {
                \Log::warning('Error fixing comments table: ' . $e->getMessage());
            }
            
            // Ensure guest_name exists
            if (!Schema::hasColumn('comments', 'guest_name')) {
                try {
                    Schema::table('comments', function (Blueprint $table) {
                        $table->string('guest_name')->nullable()->after('user_id');
                    });
                } catch (\Exception $e) {
                    \Log::warning('Error adding guest_name to comments: ' . $e->getMessage());
                }
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

