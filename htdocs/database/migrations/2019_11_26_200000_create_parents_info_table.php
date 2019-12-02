<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateParentsInfoTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('parents_info', function (Blueprint $table) {
			$table->increments('id');
			$table->string('uuid',40);
			$table->string('cn',20);
			$table->string('user_status',20);
			$table->string('sn',60)->nullable();
			$table->string('given_name',60)->nullable();
			$table->string('display_name',100);
			$table->string('mail',256)->nullable();
			$table->string('mobile',20)->nullable();
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
        Schema::dropIfExists('parents_info');
    }
}
