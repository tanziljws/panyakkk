<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Check if column already exists before adding it
        if (Schema::hasTable('galeris')) {
            $columns = Schema::getColumnListing('galeris');
            
            if (!in_array('thumbnail', $columns)) {
                Schema::table('galeris', function (Blueprint $table) {
                    $table->string('thumbnail')->nullable()->after('gambar');
                });
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('galeris')) {
            $columns = Schema::getColumnListing('galeris');
            
            if (in_array('thumbnail', $columns)) {
                Schema::table('galeris', function (Blueprint $table) {
                    $table->dropColumn('thumbnail');
                });
            }
        }
    }
};