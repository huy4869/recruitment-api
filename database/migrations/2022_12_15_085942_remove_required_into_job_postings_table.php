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
        Schema::table('job_postings', function (Blueprint $table) {
            $table->unsignedBigInteger('store_id')->nullable(true)->change();
            $table->json('job_type_ids')->nullable(true)->change();
            $table->json('work_type_ids')->nullable(true)->change();
            $table->unsignedBigInteger('job_status_id')->nullable(true)->change();
            $table->unsignedBigInteger('province_id')->nullable(true)->change();
            $table->unsignedBigInteger('salary_min')->nullable(true)->change();
            $table->unsignedBigInteger('salary_max')->nullable(true)->change();
            $table->unsignedBigInteger('salary_type_id')->nullable(true)->change();
            $table->unsignedSmallInteger('range_hours_type')->nullable(true)->change();
            $table->json('experience_ids')->nullable(true)->change();
            $table->unsignedBigInteger('views')->nullable(true)->change();
            $table->unsignedBigInteger('applies')->default(0)->nullable(true)->change();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('job_postings', function (Blueprint $table) {
            $table->unsignedBigInteger('store_id')->nullable(false)->change();
            $table->json('job_type_ids')->nullable(false)->change();
            $table->json('work_type_ids')->nullable(false)->change();
            $table->unsignedBigInteger('job_status_id')->nullable(false)->change();
            $table->unsignedBigInteger('province_id')->nullable(false)->change();
            $table->unsignedBigInteger('salary_min')->nullable(false)->change();
            $table->unsignedBigInteger('salary_max')->nullable(false)->change();
            $table->unsignedBigInteger('salary_type_id')->nullable(false)->change();
            $table->unsignedTinyInteger('range_hours_type')->nullable(false)->change();
            $table->json('experience_ids')->nullable(false)->change();
            $table->unsignedBigInteger('views')->nullable(false)->change();
            $table->unsignedBigInteger('applies')->default(0)->nullable(false)->change();
        });
    }
};
