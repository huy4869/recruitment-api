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
        Schema::table('desired_condition_users', function (Blueprint $table) {
            $table->unsignedBigInteger('user_id')->nullable()->change();
            $table->unsignedBigInteger('province_id')->nullable()->change();
            $table->unsignedBigInteger('salary_type_id')->nullable()->change();
            $table->unsignedBigInteger('salary_min')->nullable()->change();
            $table->unsignedBigInteger('salary_max')->nullable()->change();
            $table->unsignedSmallInteger('age')->nullable()->change();
            $table->json('work_type_ids')->nullable()->change();
            $table->json('job_type_ids')->nullable()->change();
            $table->json('job_experience_ids')->nullable()->change();
            $table->json('job_feature_ids')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('desired_condition_users', function (Blueprint $table) {
            $table->unsignedBigInteger('user_id')->change();
            $table->unsignedBigInteger('province_id')->change();
            $table->unsignedBigInteger('salary_type_id')->change();
            $table->unsignedBigInteger('salary_min')->change();
            $table->unsignedBigInteger('salary_max')->change();
            $table->unsignedSmallInteger('age')->change();
            $table->json('work_type_ids')->change();
            $table->json('job_type_ids')->change();
            $table->json('job_experience_ids')->change();
            $table->json('job_feature_ids')->change();
        });
    }
};
