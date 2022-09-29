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
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('role_id');
            $table->string('first_name', 255)->nullable();
            $table->string('last_name', 255)->nullable();
            $table->string('furi_first_name', 255)->nullable();
            $table->string('furi_last_name', 255)->nullable();
            $table->string('alias_name', 255)->nullable();
            $table->date('birthday')->nullable();
            $table->unsignedTinyInteger('age')->nullable();
            $table->unsignedBigInteger('gender_id')->nullable();
            $table->string('tel', 20)->nullable();
            $table->string('email', 255);
            $table->timestamp('email_verified_at')->nullable();
            $table->string('line', 255)->nullable();
            $table->string('facebook', 255)->nullable();
            $table->string('instagram', 255)->nullable();
            $table->string('twitter', 255)->nullable();
            $table->string('postal_code', 255)->nullable();
            $table->unsignedBigInteger('province_id')->nullable();
            $table->string('city', 255)->nullable();
            $table->string('address', 255)->nullable();
            $table->string('password', 255);
            $table->string('remember_token', 100)->nullable();
            $table->text('favorite')->nullable();
            $table->text('skill')->nullable();
            $table->text('experience')->nullable();
            $table->text('knowledge')->nullable();
            $table->text('selft_pr')->nullable();
            $table->unsignedBigInteger('desire_province_id')->nullable();
            $table->unsignedBigInteger('desire_job_type_id')->nullable();
            $table->timestamp('desire_from_working')->nullable();
            $table->timestamp('desire_to_working')->nullable();
            $table->unsignedBigInteger('desire_job_work_id')->nullable();
            $table->unsignedBigInteger('desire_from_salary')->nullable();
            $table->unsignedBigInteger('desire_to_salary')->nullable();
            $table->unsignedBigInteger('experience_id')->nullable();
            $table->string('home_page_rescuiter', 255)->nullable();
            $table->text('motivation')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('role_id')->references('id')->on('m_roles')->onDelete('cascade');
            $table->foreign('gender_id')->references('id')->on('m_genders')->onDelete('cascade');
            $table->foreign('province_id')->references('id')->on('m_provinces')->onDelete('cascade');
            $table->foreign('experience_id')->references('id')->on('m_job_experiences')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('users');
    }
};
