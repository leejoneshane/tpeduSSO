<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateSocialiteAccountTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('socialite_account', function (Blueprint $table) {
            $table->string('idno')->primary();
			$table->string('socialite');
			$table->string('userId',255);
            $table->timestamps();
            $table->index(['idno', 'socialite'])->unique();
            $table->index(['socialite', 'userId'])->unique();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('socialite_account');
    }
}
