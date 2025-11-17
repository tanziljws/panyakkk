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
        // Check if migrations table exists and fix its structure
        if (Schema::hasTable('migrations')) {
            $driver = Schema::getConnection()->getDriverName();
            
            if ($driver === 'mysql') {
                // Check if id column has AUTO_INCREMENT
                $columns = DB::select("SHOW COLUMNS FROM `migrations` WHERE Field = 'id'");
                
                if (!empty($columns)) {
                    $column = $columns[0];
                    // Check if Extra contains 'auto_increment'
                    if (strpos(strtolower($column->Extra ?? ''), 'auto_increment') === false) {
                        // Fix the id column to have AUTO_INCREMENT
                        DB::statement('ALTER TABLE `migrations` MODIFY `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT');
                    }
                } else {
                    // If id column doesn't exist, recreate the table properly
                    Schema::dropIfExists('migrations');
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

