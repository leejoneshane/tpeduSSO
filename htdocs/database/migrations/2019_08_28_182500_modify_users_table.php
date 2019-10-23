<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class ModifyUsersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
   
		 public function up() {
		    Schema::table('users', function (Blueprint $table) {
		
		        $table->tinyInteger('is_change_account')->default(0)->after('is_admin');
		        $table->tinyInteger('is_change_password')->default(0)->after('is_admin');
		
		    });
		}   

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('users');
    }
}
