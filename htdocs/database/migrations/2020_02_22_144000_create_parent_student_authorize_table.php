<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateParentStudentAuthorizeTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('parent_student_authorize', function (Blueprint $table) {
            $table->increments('id');
			$table->string('parent_idno');
			$table->string('student_idno');
            $table->string('client_id')->default('*');
            $table->integer('trust_level')->default(0);
            $table->timestamps();
            $table->index(['student_idno', 'client_id'])->unique();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('parent_student_link');
    }
}
