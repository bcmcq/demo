<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Add missing foreign key constraints, composite indices, and sort indices
     * to improve query performance across API endpoints and autopost selection.
     */
    public function up(): void
    {
        Schema::table('social_media_contents', function (Blueprint $table) {
            $table->foreign('social_media_category_id')
                ->references('id')
                ->on('social_media_categories')
                ->cascadeOnDelete();

            $table->index(['account_id', 'social_media_category_id'], 'contents_account_category_index');
        });

        Schema::table('social_media_posts', function (Blueprint $table) {
            $table->index('posted_at');
        });

        Schema::table('social_media_schedules', function (Blueprint $table) {
            $table->index('scheduled_at');
        });

        Schema::table('social_media_account_category_weights', function (Blueprint $table) {
            $table->unique(
                ['account_id', 'social_media_category_id'],
                'account_category_weights_unique'
            );
        });

        Schema::table('content_generation_requests', function (Blueprint $table) {
            $table->index('status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('social_media_contents', function (Blueprint $table) {
            $table->dropForeign(['social_media_category_id']);
            $table->dropIndex('contents_account_category_index');
        });

        Schema::table('social_media_posts', function (Blueprint $table) {
            $table->dropIndex(['posted_at']);
        });

        Schema::table('social_media_schedules', function (Blueprint $table) {
            $table->dropIndex(['scheduled_at']);
        });

        Schema::table('social_media_account_category_weights', function (Blueprint $table) {
            $table->dropUnique('account_category_weights_unique');
        });

        Schema::table('content_generation_requests', function (Blueprint $table) {
            $table->dropIndex(['status']);
        });
    }
};
