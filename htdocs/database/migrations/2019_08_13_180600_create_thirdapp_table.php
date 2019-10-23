<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateThirdappTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('thirdapp', function (Blueprint $table) {
            $table->increments('id');
			$table->string('unit',150);
			$table->string('entry',150);
			$table->string('background',200)->nullable();
			$table->string('url',255)->unique();
			$table->string('redirect',255);
			$table->string('unittype',10)->nullable();
			$table->string('conman',100)->nullable();
			$table->string('conmail',255)->nullable();
			$table->string('conunit',100)->nullable();
			$table->string('contel',100)->nullable();
			$table->string('scope',100)->nullable();
			$table->string('authyn',10)->nullable();
			$table->string('recno',60)->nullable();
            $table->string('recdt',20)->nullable();
			$table->string('key',40)->nullable()->unique();
			$table->string('stopyn',10)->nullable();
			$table->string('stopdt',20)->nullable();
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
        Schema::dropIfExists('thirdapp');
    }
}
