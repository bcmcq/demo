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
        Schema::table('content_generation_requests', function (Blueprint $table) {
            $table->dropForeign(['social_media_content_id']);
            $table->foreign('social_media_content_id')
                ->references('id')
                ->on('social_media_contents')
                ->cascadeOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('content_generation_requests', function (Blueprint $table) {
            $table->dropForeign(['social_media_content_id']);
            $table->foreign('social_media_content_id')
                ->references('id')
                ->on('social_media_contents')
                ->nullOnDelete();
        });
    }
};
