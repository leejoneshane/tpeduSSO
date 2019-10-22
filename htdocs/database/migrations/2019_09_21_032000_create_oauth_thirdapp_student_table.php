<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateOauthThirdappStudentTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
    	// type 1 全授權 0 個別授權  學生idno 家長idno 第三方id
        Schema::create('oauth_thirdapp_student', function (Blueprint $table) {
            $table->increments('id');
						$table->string('student_idno',20);
						$table->string('parent_idno',20);
						$table->string('type',1)->nullable();
						$table->integer('thirdapp_id')->nullable(); 
						$table->timestamps(); 
						$table->index(['student_idno', 'parent_idno']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('oauth_thirdapp_student');
    }
}
 