<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateStudentParentsQrcodeTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('student_parents_qrcode', function (Blueprint $table) {
            $table->increments('id');
			$table->string('guid',50)->unique();
			$table->integer('dataid');
			$table->string('std_name',40);
			$table->string('std_cls',20)->nullable();
			$table->string('std_seat',10)->nullable();
			$table->string('par_name',40);
			$table->string('par_rel',20)->nullable();
			$table->string('expire_date',20);
			$table->string('created_user',40)->nullable();
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
        Schema::dropIfExists('student_parents_qrcode');
    }
}
