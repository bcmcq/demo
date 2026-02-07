<?php

use App\Models\Account;
use App\Models\SocialMediaCategory;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSocialMediaContentsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('social_media_contents', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(Account::class)->constrained();
            $table->foreignIdFor(SocialMediaCategory::class);
            $table->string('title');
            $table->text('content');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('social_media_contents');
    }
}
