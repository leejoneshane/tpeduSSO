<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateStudentParentApplyTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {      //學校代號 學號 學生身分號 生日 家長身分號 家長姓名 關係 電話 EMAIL 狀態:0待審核,1通過,2不通過 審核意見
        Schema::create('student_parent_apply', function (Blueprint $table) {
			$table->increments('id');
			$table->string('school_id',10);
			//$table->string('student_id',20);
			$table->string('student_idno',20);
			//$table->string('student_birthday',10)->nullable();
			$table->string('parent_idno',20);
			$table->string('parent_name',20);
			$table->string('parent_relation',20);
			$table->string('parent_mobile',50);
			$table->string('parent_email',150);
			$table->string('status',1)->default('0');
			$table->string('cause',150)->nullable();
			$table->timestamp('verify_tm')->nullable();

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
        Schema::dropIfExists('student_parent_apply');
    }
}
