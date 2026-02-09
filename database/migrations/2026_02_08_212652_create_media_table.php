<?php

use App\Models\SocialMediaContent;
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
        Schema::create('media', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(SocialMediaContent::class)->constrained()->cascadeOnDelete();
            $table->string('type');
            $table->string('file_path')->nullable();
            $table->string('file_name');
            $table->string('mime_type')->nullable();
            $table->unsignedBigInteger('size')->nullable();
            $table->string('mux_asset_id')->nullable();
            $table->string('mux_playback_id')->nullable();
            $table->string('mux_upload_id')->nullable();
            $table->string('mux_status')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('media');
    }
};
