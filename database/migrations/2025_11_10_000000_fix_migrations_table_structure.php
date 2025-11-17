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
        
        // Check if migrations table exists and fix its structure
        if (Schema::hasTable('migrations')) {
            if ($driver === 'mysql') {
                try {
                    // Check if id column has AUTO_INCREMENT
                    $columns = DB::select("SHOW COLUMNS FROM `migrations` WHERE Field = 'id'");
                    
                    if (!empty($columns)) {
                        $column = $columns[0];
                        // Check if Extra contains 'auto_increment'
                        $extra = strtolower($column->Extra ?? '');
                        if (strpos($extra, 'auto_increment') === false) {
                            // Get the current column type
                            $type = $column->Type ?? 'bigint unsigned';
                            
                            // Fix the id column to have AUTO_INCREMENT
                            // Check if it's already a primary key
                            $isPrimary = false;
                            try {
                                $keys = DB::select("SHOW KEYS FROM `migrations` WHERE Key_name = 'PRIMARY' AND Column_name = 'id'");
                                $isPrimary = !empty($keys);
                            } catch (\Exception $e) {
                                // Assume it's a primary key if we can't check
                                $isPrimary = true;
                            }
                            
                            if ($isPrimary) {
                                DB::statement('ALTER TABLE `migrations` MODIFY `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT');
                            } else {
                                DB::statement('ALTER TABLE `migrations` MODIFY `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY');
                            }
                        }
                    } else {
                        // If id column doesn't exist, recreate the table properly
                        // First, backup existing migration records if any
                        $existingMigrations = [];
                        try {
                            $existingMigrations = DB::table('migrations')->get()->toArray();
                        } catch (\Exception $e) {
                            // If we can't read, table is broken, proceed with recreation
                        }
                        
                        Schema::dropIfExists('migrations');
                        Schema::create('migrations', function (Blueprint $table) {
                            $table->id();
                            $table->string('migration');
                            $table->integer('batch');
                        });
                        
                        // Restore migration records if we backed them up
                        if (!empty($existingMigrations)) {
                            foreach ($existingMigrations as $migration) {
                                try {
                                    DB::table('migrations')->insert([
                                        'migration' => $migration->migration,
                                        'batch' => $migration->batch,
                                    ]);
                                } catch (\Exception $e) {
                                    // Skip if insert fails
                                }
                            }
                        }
                    }
                } catch (\Exception $e) {
                    // If table is completely broken, recreate it
                    try {
                        Schema::dropIfExists('migrations');
                    } catch (\Exception $e2) {
                        // Ignore drop errors
                    }
                    
                    Schema::create('migrations', function (Blueprint $table) {
                        $table->id();
                        $table->string('migration');
                        $table->integer('batch');
                    });
                }
            }
        } else {
            // Create migrations table if it doesn't exist
            Schema::create('migrations', function (Blueprint $table) {
                $table->id();
                $table->string('migration');
                $table->integer('batch');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // This migration is safe to run multiple times, no need to reverse
    }
};

