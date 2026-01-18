<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('user_auths', function (Blueprint $table) {
            $table->boolean('is_first_login')->default(true);
            $table->integer('login_attempts')->default(0);
            $table->boolean('is_locked')->default(false);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('user_auths', function (Blueprint $table) {
            $table->dropColumn('is_first_login');
            $table->dropColumn('login_attempts');
            $table->dropColumn('is_locked');
        });
    }
};
