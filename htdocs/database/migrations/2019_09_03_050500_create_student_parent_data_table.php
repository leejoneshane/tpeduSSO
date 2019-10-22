<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateStudentParentDataTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {      //學校代號 學號 學生身份字號  生日 家長身分字號   家長姓名 關係 電話  EMAIL 狀態0 未綁定 1綁定過
        Schema::create('student_parent_data', function (Blueprint $table) {
            $table->increments('id');
						$table->string('school_id',10);
						$table->string('student_id',20);
						$table->string('student_idno',20);
						$table->string('student_birthday',10)->nullable();
						$table->string('parent_idno',20)->nullable();
						$table->string('parent_name',20);
						$table->string('parent_relation',20)->nullable();
						$table->string('parent_mobile',100)->nullable();
						$table->string('parent_email',150)->nullable();
						$table->string('status',1)->default('0');

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
        Schema::dropIfExists('student_parent_data');
    }
}
