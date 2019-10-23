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
			$table->timestamp('gsuite_created_at')->nullable();
			$table->string('gsuite_email')->nullable();
		});
	}

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
	Schema::table('users', function (Blueprint $table) {
		$table->dropColume(['gsuite_created_at', 'gsuite_email']);
	});
    }
}
