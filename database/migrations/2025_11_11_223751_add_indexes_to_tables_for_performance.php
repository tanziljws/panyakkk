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
            if (!Schema::hasIndex('galeris', 'galeris_category_id_created_at_index')) {
                $table->index(['category_id', 'created_at'], 'galeris_category_id_created_at_index');
            }
            if (!Schema::hasIndex('galeris', 'galeris_created_at_index')) {
                $table->index('created_at', 'galeris_created_at_index');
            }
            if (!Schema::hasIndex('galeris', 'galeris_judul_index')) {
                $table->index('judul', 'galeris_judul_index');
            }
        });

        // Add indexes to users table
        Schema::table('users', function (Blueprint $table) {
            if (!Schema::hasIndex('users', 'users_role_status_index')) {
                $table->index(['role', 'status'], 'users_role_status_index');
            }
            if (!Schema::hasIndex('users', 'users_created_at_index')) {
                $table->index('created_at', 'users_created_at_index');
            }
        });

        // Add indexes to comments table
        Schema::table('comments', function (Blueprint $table) {
            if (!Schema::hasIndex('comments', 'comments_galeri_id_created_at_index')) {
                $table->index(['galeri_id', 'created_at'], 'comments_galeri_id_created_at_index');
            }
            if (!Schema::hasIndex('comments', 'comments_user_id_index')) {
                $table->index('user_id', 'comments_user_id_index');
            }
        });

        // Add indexes to likes table
        Schema::table('likes', function (Blueprint $table) {
            if (!Schema::hasIndex('likes', 'likes_galeri_id_created_at_index')) {
                $table->index(['galeri_id', 'created_at'], 'likes_galeri_id_created_at_index');
            }
            if (!Schema::hasIndex('likes', 'likes_user_id_index')) {
                $table->index('user_id', 'likes_user_id_index');
            }
        });

        // Add indexes to categories table
        Schema::table('categories', function (Blueprint $table) {
            if (!Schema::hasIndex('categories', 'categories_slug_index')) {
                $table->index('slug', 'categories_slug_index');
            }
        });

        // Add indexes to activity_logs table (check if index exists first)
        Schema::table('activity_logs', function (Blueprint $table) {
            if (!Schema::hasIndex('activity_logs', 'activity_logs_user_type_created_at_index')) {
                $table->index(['user_type', 'created_at'], 'activity_logs_user_type_created_at_index');
            }
            if (!Schema::hasIndex('activity_logs', 'activity_logs_activity_type_index')) {
                $table->index('activity_type', 'activity_logs_activity_type_index');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('galeris', function (Blueprint $table) {
            if (Schema::hasIndex('galeris', 'galeris_category_id_created_at_index')) {
                $table->dropIndex('galeris_category_id_created_at_index');
            }
            if (Schema::hasIndex('galeris', 'galeris_created_at_index')) {
                $table->dropIndex('galeris_created_at_index');
            }
            if (Schema::hasIndex('galeris', 'galeris_judul_index')) {
                $table->dropIndex('galeris_judul_index');
            }
        });

        Schema::table('users', function (Blueprint $table) {
            if (Schema::hasIndex('users', 'users_role_status_index')) {
                $table->dropIndex('users_role_status_index');
            }
            if (Schema::hasIndex('users', 'users_created_at_index')) {
                $table->dropIndex('users_created_at_index');
            }
        });

        Schema::table('comments', function (Blueprint $table) {
            if (Schema::hasIndex('comments', 'comments_galeri_id_created_at_index')) {
                $table->dropIndex('comments_galeri_id_created_at_index');
            }
            if (Schema::hasIndex('comments', 'comments_user_id_index')) {
                $table->dropIndex('comments_user_id_index');
            }
        });

        Schema::table('likes', function (Blueprint $table) {
            if (Schema::hasIndex('likes', 'likes_galeri_id_created_at_index')) {
                $table->dropIndex('likes_galeri_id_created_at_index');
            }
            if (Schema::hasIndex('likes', 'likes_user_id_index')) {
                $table->dropIndex('likes_user_id_index');
            }
        });

        Schema::table('categories', function (Blueprint $table) {
            if (Schema::hasIndex('categories', 'categories_slug_index')) {
                $table->dropIndex('categories_slug_index');
            }
        });

        Schema::table('activity_logs', function (Blueprint $table) {
            if (Schema::hasIndex('activity_logs', 'activity_logs_user_type_created_at_index')) {
                $table->dropIndex('activity_logs_user_type_created_at_index');
            }
            if (Schema::hasIndex('activity_logs', 'activity_logs_activity_type_index')) {
                $table->dropIndex('activity_logs_activity_type_index');
            }
        });
    }
};