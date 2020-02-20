<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateProjectsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('projects', function (Blueprint $table) {
            $table->increments('id');
            $table->string('organizaton',150);
            $table->string('applicationName',150);
            $table->string('reason',255);
            $table->string('website',255);
            $table->string('redirect',255);
            $table->integer('kind')->default(0); //1->本局 2->學校 3->廠商
            $table->string('connName',50);
            $table->string('connUnit',150);
            $table->string('connEmail',255);
            $table->string('connTel',255);
            $table->string('memo',255)->nullable();
            $table->tinyInteger('audit')->default(0); //1->pass
            $table->string('clients',255)->nullable(); //split by ,
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
        Schema::dropIfExists('projects');
    }
}
