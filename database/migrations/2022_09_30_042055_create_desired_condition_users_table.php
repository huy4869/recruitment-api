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
        Schema::create('desired_condition_users', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->unsignedBigInteger('province_id');
            $table->unsignedBigInteger('salary_type_id');
            $table->unsignedBigInteger('salary_min');
            $table->unsignedBigInteger('salary_max');
            $table->unsignedSmallInteger('age');
            $table->json('work_type_ids');
            $table->json('job_type_ids');
            $table->json('job_experience_ids');
            $table->json('job_feature_ids');
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('province_id')->references('id')->on('m_provinces')->onDelete('cascade');
            $table->foreign('salary_type_id')->references('id')->on('m_salary_types')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('desired_condition_users');
    }
};
