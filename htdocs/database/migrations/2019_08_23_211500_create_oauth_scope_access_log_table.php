<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateOauthScopeAccessLogTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('oauth_scope_access_log', function (Blueprint $table) {
            $table->increments('id');
						$table->string('system_id',150)->nullable();
						$table->string('authorizer',150)->nullable();
						$table->string('approve',150)->nullable();
						$table->string('scope',100)->nullable();
						$table->string('scope_range',250)->nullable();
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
        Schema::dropIfExists('oauth_scope_access_log');
    }
}
