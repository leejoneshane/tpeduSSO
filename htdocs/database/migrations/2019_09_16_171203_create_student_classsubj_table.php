<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateStudentClasssubjTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('student_classsubj', function (Blueprint $table) {
            $table->increments('id');
			$table->string('uuid',36);
			$table->string('subjkey',100);
			$table->string('school',50)->nullable();
			$table->string('clsid',50)->nullable();
			$table->string('subjid',50)->nullable();
            $table->timestamps();

			$table->unique(['uuid', 'subjkey']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('student_classsubj');
    }
}
