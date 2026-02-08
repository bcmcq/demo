<?php

use App\Models\Account;
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
        Schema::create('content_generation_requests', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignIdFor(Account::class)->constrained();
            $table->string('type');
            $table->text('prompt');
            $table->string('platform');
            $table->string('tone');
            $table->string('status')->default('pending');
            $table->text('generated_content')->nullable();
            $table->text('error')->nullable();
            $table->foreignId('social_media_content_id')->nullable()->constrained('social_media_contents')->nullOnDelete();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('content_generation_requests');
    }
};
