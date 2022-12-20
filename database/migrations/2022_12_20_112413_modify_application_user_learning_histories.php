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
        Schema::table('application_user_learning_histories', function (Blueprint $table) {
            $table->unsignedBigInteger('learning_status_id')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('application_user_learning_histories', function (Blueprint $table) {
            $table->unsignedBigInteger('learning_status_id')->change();
        });
    }
};
