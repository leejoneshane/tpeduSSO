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
<<<<<<< HEAD
<<<<<<< HEAD
=======
			$table->string('gsuite_email')->nullable();
>>>>>>> parent of 071b0327... Delete 2019_09_27_170218_modify_users_table.php
=======
>>>>>>> parent of 3b03811b... Update 2019_09_27_170218_modify_users_table.php
		});
	}

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
<<<<<<< HEAD
<<<<<<< HEAD
        Schema::dropIfExists('users');
    }
}
=======
	Schema::table('users', function (Blueprint $table) {
		$table->dropColume(['gsuite_created_at', 'gsuite_email']);
	});
    }
}
>>>>>>> parent of 071b0327... Delete 2019_09_27_170218_modify_users_table.php
=======
        Schema::dropIfExists('users');
    }
}
>>>>>>> parent of 3b03811b... Update 2019_09_27_170218_modify_users_table.php
