<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateOauthScopeFieldTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('oauth_scope_field', function (Blueprint $table) {
            $table->increments('id');
						$table->string('scope',50);
						$table->string('field_key',100);
						$table->string('field_cname',100)->nullable();
						$table->string('field_description',300)->nullable();
						$table->timestamps(); 
						$table->unique(['scope', 'field_key']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('oauth_scope_field');
    }
}
 