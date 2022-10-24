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
        Schema::table('user_work_histories', function (Blueprint $table) {
            $table->dropColumn(['job_type_ids', 'work_type_ids']);
            $table->unsignedBigInteger('job_type_id')->after('user_id');
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
        Schema::table('user_work_histories', function (Blueprint $table) {
            $table->dropColumn(['job_type_id', 'work_type_id']);
            $table->json('job_type_ids')->after('user_id');
            $table->json('work_type_ids')->after('job_type_ids');
        });
    }
};
