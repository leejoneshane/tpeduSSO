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
            $table->uuid('uuid')->primary();
            $table->string('organization',150)->nullable();
            $table->string('applicationName',150);
            $table->string('reason',255)->nullable();
            $table->string('website',255)->nullable();
            $table->string('redirect',255);
            $table->integer('kind')->default(0); //1->本局 2->學校 3->廠商
            $table->string('connName',50)->nullable();
            $table->string('connUnit',150)->nullable();
            $table->string('connEmail',255)->nullable();
            $table->string('connTel',255)->nullable();
            $table->string('memo',255)->nullable();
            $table->boolean('audit')->default(0); //1->pass
            $table->integer('client')->nullable()->index();
            $table->boolean('privileged')->default(0);
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
