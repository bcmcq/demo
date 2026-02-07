<?php

use App\Models\Account;
use App\Models\SocialMediaContent;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSocialMediaSchedules extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('social_media_schedules', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->foreignIdFor(Account::class)->constrained();
            $table->foreignIdFor(SocialMediaContent::class)->constrained();
            $table->dateTime('scheduled_at');
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
        Schema::dropIfExists('social_media_schedules');
    }
}
