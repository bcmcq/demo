<?php

use App\Models\Account;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSocialMediaAccountCategoryWeightsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('social_media_account_category_weights', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->foreignIdFor(Account::class)->constrained();
            $table->bigInteger('social_media_category_id')->unsigned();
            $table->tinyInteger('weight')->unsigned();
            $table->timestamps();

            // Can't use `foreignIdFor` because the name is too long 😞
            $table->foreign('social_media_category_id', 'social_media_account_category_weights_category_id_foreign')
                ->references('id')
                ->on('social_media_categories')
                ->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('social_media_account_category_weights');
    }
}
