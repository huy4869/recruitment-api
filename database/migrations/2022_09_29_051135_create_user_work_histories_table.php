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
        Schema::create('user_work_histories', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->unsignedBigInteger('job_type_id');
            $table->unsignedBigInteger('work_type_id');
            $table->string('store_name', 255)->nullable();
            $table->string('company_name', 255)->nullable();
            $table->dateTime('period_start')->nullable();
            $table->dateTime('period_end')->nullable();
            $table->text('position_offices')->nullable();
            $table->string('business_content', 255)->nullable();
            $table->text('experience_accumulation')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('job_type_id')->references('id')->on('m_job_types')->onDelete('cascade');
            $table->foreign('work_type_id')->references('id')->on('m_work_types')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('user_work_histories');
    }
};
