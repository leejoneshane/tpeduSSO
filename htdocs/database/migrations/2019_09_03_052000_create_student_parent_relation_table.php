<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateStudentParentRelationTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {      //學生身份字號  生日 家長身分字號   家長姓名 關係 狀態 0解除連結   1連結
        Schema::create('student_parent_relation', function (Blueprint $table) {
            $table->increments('id');
						$table->string('student_idno',20);
						$table->string('student_birthday',10)->nullable();
						$table->string('parent_idno',20);
						$table->string('parent_name',20)->nullable();
						$table->string('parent_relation',20)->nullable();
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
        Schema::dropIfExists('student_parent_relation');
    }
}
