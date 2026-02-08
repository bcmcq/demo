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
            $table->json('generated_content')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('content_generation_requests', function (Blueprint $table) {
            $table->text('generated_content')->nullable()->change();
        });
    }
};
