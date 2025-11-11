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
        // Add indexes to galeris table
        Schema::table('galeris', function (Blueprint $table) {
            $table->index(['category_id', 'created_at']);
            $table->index('created_at');
            $table->index('judul');
        });

        // Add indexes to users table
        Schema::table('users', function (Blueprint $table) {
            $table->index(['role', 'status']);
            $table->index('created_at');
        });

        // Add indexes to comments table
        Schema::table('comments', function (Blueprint $table) {
            $table->index(['galeri_id', 'created_at']);
            $table->index('user_id');
        });

        // Add indexes to likes table
        Schema::table('likes', function (Blueprint $table) {
            $table->index(['galeri_id', 'created_at']);
            $table->index('user_id');
        });

        // Add indexes to categories table
        Schema::table('categories', function (Blueprint $table) {
            $table->index('slug');
        });

        // Add indexes to activity_logs table (check if index exists first)
        Schema::table('activity_logs', function (Blueprint $table) {
            if (!Schema::hasIndex('activity_logs', 'activity_logs_user_type_created_at_index')) {
                $table->index(['user_type', 'created_at']);
            }
            if (!Schema::hasIndex('activity_logs', 'activity_logs_activity_type_index')) {
                $table->index('activity_type');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('galeris', function (Blueprint $table) {
            $table->dropIndex(['category_id', 'created_at']);
            $table->dropIndex(['created_at']);
            $table->dropIndex(['judul']);
        });

        Schema::table('users', function (Blueprint $table) {
            $table->dropIndex(['role', 'status']);
            $table->dropIndex(['created_at']);
        });

        Schema::table('comments', function (Blueprint $table) {
            $table->dropIndex(['galeri_id', 'created_at']);
            $table->dropIndex(['user_id']);
        });

        Schema::table('likes', function (Blueprint $table) {
            $table->dropIndex(['galeri_id', 'created_at']);
            $table->dropIndex(['user_id']);
        });

        Schema::table('categories', function (Blueprint $table) {
            $table->dropIndex(['slug']);
        });

        Schema::table('activity_logs', function (Blueprint $table) {
            if (Schema::hasIndex('activity_logs', 'activity_logs_user_type_created_at_index')) {
                $table->dropIndex(['user_type', 'created_at']);
            }
            if (Schema::hasIndex('activity_logs', 'activity_logs_activity_type_index')) {
                $table->dropIndex(['activity_type']);
            }
        });
    }
};