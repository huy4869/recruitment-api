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
        Schema::create('job_postings', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('store_id');
            $table->unsignedBigInteger('job_type_id');
            $table->json('work_type_ids');
            $table->unsignedBigInteger('job_status_id');
            $table->string('postal_code', 255)->nullable();
            $table->unsignedBigInteger('province_id');
            $table->string('city', 255)->nullable();
            $table->string('address', 255)->nullable();
            $table->json('stations')->nullable();
            $table->string('name', 255)->nullable();
            $table->text('pick_up_point')->nullable();
            $table->text('description')->nullable();
            $table->text('welfare_treatment_description')->nullable();
            $table->unsignedBigInteger('salary_min');
            $table->unsignedBigInteger('salary_max');
            $table->unsignedBigInteger('salary_type_id');
            $table->string('salary_description', 255)->nullable();
            $table->string('start_work_time', 20)->nullable();
            $table->string('end_work_time', 20)->nullable();
            $table->text('shifts')->nullable();
            $table->json('gender_ids')->nullable();
            $table->text('holiday_description')->nullable();
            $table->json('feature_ids')->nullable();
            $table->unsignedBigInteger('views');
            $table->unsignedTinyInteger('age_min');
            $table->unsignedTinyInteger('age_max');
            $table->unsignedBigInteger('created_by');
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('store_id')->references('id')->on('stores')->onDelete('cascade');
            $table->foreign('job_type_id')->references('id')->on('m_job_types')->onDelete('cascade');
            $table->foreign('job_status_id')->references('id')->on('m_job_statuses')->onDelete('cascade');
            $table->foreign('province_id')->references('id')->on('m_provinces')->onDelete('cascade');
            $table->foreign('salary_type_id')->references('id')->on('m_salary_types')->onDelete('cascade');
            $table->foreign('created_by')->references('id')->on('users')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('job_postings');
    }
};
