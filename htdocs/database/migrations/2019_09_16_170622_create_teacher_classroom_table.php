<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTeacherClassroomTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('teacher_classroom', function (Blueprint $table) {
            $table->increments('id');
			$table->string('uuid',36);
			$table->string('subjkey',100);
			$table->string('enrollment_code',50)->nullable();
			$table->string('classroom_id',50)->nullable();
			$table->string('alternate_link',100)->nullable();
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
        Schema::dropIfExists('teacher_classroom');
    }
}
