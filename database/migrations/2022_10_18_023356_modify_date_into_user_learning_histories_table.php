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
        Schema::table('user_learning_histories', function (Blueprint $table) {
            $table->char('enrollment_period_start', 6)->change();
            $table->char('enrollment_period_end', 6)->change();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('user_learning_histories', function (Blueprint $table) {
            $table->dateTime('enrollment_period_start')->change();
            $table->dateTime('enrollment_period_end')->change();
        });
    }
};
