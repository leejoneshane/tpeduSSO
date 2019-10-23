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
            $table->string('gsuite_email')->nullable()->after('is_admin');
            $table->timestamp('gsuite_created_at')->nullable()->after('is_admin');
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
            $table->dropColume(['gsuite_email', 'gsuite_created_at']);
        });
    }
}