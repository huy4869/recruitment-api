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
        Schema::table('application_user_work_histories', function (Blueprint $table) {
            $table->unsignedBigInteger('work_type_id')->after('job_type_id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('application_user_work_histories', function (Blueprint $table) {
            $table->dropColumn('work_type_id');
        });
    }
};
