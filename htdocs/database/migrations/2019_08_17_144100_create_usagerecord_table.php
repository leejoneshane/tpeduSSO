<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateUsagerecordTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('usagerecord', function (Blueprint $table) {
            $table->increments('id');
			$table->string('userid',200)->nullable();
			$table->string('username',200)->nullable();
			$table->string('ipaddress',40)->nullable();
			$table->string('eventtime',30)->nullable();
			$table->string('module',100)->nullable();
			$table->string('content',2000)->nullable();
			$table->string('note',2000)->nullable();
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
        Schema::dropIfExists('usagerecord');
    }
}
