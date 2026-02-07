<?php

use App\Models\Account;
use App\Models\SocialMediaContent;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSocialMediaPosts extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('social_media_posts', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->foreignIdFor(Account::class)->constrained();
            $table->foreignIdFor(SocialMediaContent::class)->constrained();
            $table->dateTime('posted_at');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('social_media_posts');
    }
}
