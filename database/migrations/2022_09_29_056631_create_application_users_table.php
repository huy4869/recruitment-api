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
        Schema::dropIfExists('application_users');
        Schema::create('application_users', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('role_id');
            $table->string('first_name', 255)->nullable();
            $table->string('last_name', 255)->nullable();
            $table->string('furi_first_name', 255)->nullable();
            $table->string('furi_last_name', 255)->nullable();
            $table->string('alias_name', 255)->nullable();
            $table->timestamp('birthday')->nullable();
            $table->unsignedTinyInteger('age')->nullable();
            $table->unsignedBigInteger('gender_id')->nullable();
            $table->string('tel', 20)->nullable();
            $table->string('email', 100);
            $table->string('facebook', 255)->nullable();
            $table->string('line', 255)->nullable();
            $table->string('instagram', 255)->nullable();
            $table->string('twitter', 255)->nullable();
            $table->string('postal_code', 255)->nullable();
            $table->unsignedBigInteger('province_id')->nullable();
            $table->string('city', 255)->nullable();
            $table->string('address', 255)->nullable();
            $table->unsignedBigInteger('image_id')->nullable();
            $table->json('achievement_imgs')->nullable();
            $table->text('favorite')->nullable();
            $table->text('skill')->nullable();
            $table->text('experience')->nullable();
            $table->text('knowledge')->nullable();
            $table->text('self_pr')->nullable();
            $table->unsignedBigInteger('desire_city_id');
            $table->unsignedBigInteger('desire_job_id');
            $table->unsignedBigInteger('desire_job_work_id');
            $table->unsignedBigInteger('desire_salary');
            $table->unsignedBigInteger('experience_year');
            $table->string('home_page_recruiter', 255)->nullable();
            $table->text('motivation')->nullable();
            $table->text('noteworthy_things')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('role_id')->references('id')->on('m_roles')->onDelete('cascade');
            $table->foreign('province_id')->references('id')->on('m_provinces')->onDelete('cascade');
            $table->foreign('gender_id')->references('id')->on('m_genders')->onDelete('cascade');
            $table->foreign('image_id')->references('id')->on('images')->onDelete('cascade');
            $table->foreign('desire_job_id')->references('id')->on('m_job_types')->onDelete('cascade');
            $table->foreign('desire_city_id')->references('id')->on('m_provinces')->onDelete('cascade');
            $table->foreign('desire_job_work_id')->references('id')->on('m_work_types')->onDelete('cascade');
            $table->foreign('desire_salary')->references('id')->on('m_salary_types')->onDelete('cascade');
            $table->foreign('experience_year')->references('id')->on('m_job_experiences')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('application_users');
    }
};
