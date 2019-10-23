<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateOauthSocialiteAccountTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('oauth_socialite_account', function (Blueprint $table) {
            $table->increments('id');
						$table->string('source',10);
						$table->string('idno',150)->nullable();
						$table->string('oauth_id',150)->nullable();
						$table->string('email',150)->nullable();
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
        Schema::dropIfExists('oauth_socialite_account');
    }
}
