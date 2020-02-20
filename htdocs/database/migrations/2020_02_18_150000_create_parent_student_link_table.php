<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateParentStudentLinkTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('parent_student_link', function (Blueprint $table) {
            $table->increments('id');
			$table->string('parent_idno');
			$table->string('student_idno');
			$table->string('relation')->nullable();
            $table->tinyInteger('verified')->default('0');
            $table->string('verified_idno')->nullable();
            $table->string('denyReason',150)->nullable();
            $table->timestamp('verified_time')->nullable();
            $table->timestamps();
            $table->index(['parent_idno', 'student_idno'])->unique();
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
