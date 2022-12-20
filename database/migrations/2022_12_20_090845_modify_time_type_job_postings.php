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
            $table->dropColumn(['start_work_time_type', 'end_work_time_type']);
        });

        Schema::table('job_postings', function (Blueprint $table) {
            $table->unsignedTinyInteger('start_work_time_type')->nullable()->after('start_work_time');
            $table->unsignedTinyInteger('end_work_time_type')->nullable()->after('end_work_time');
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
            $table->dropColumn(['start_work_time_type', 'end_work_time_type']);
        });

        Schema::table('job_postings', function (Blueprint $table) {
            $table->unsignedTinyInteger('start_work_time_type')->default(\App\Models\JobPosting::TYPE_MORNING)->after('start_work_time');
            $table->unsignedTinyInteger('end_work_time_type')->default(\App\Models\JobPosting::TYPE_AFTERNOON)->after('end_work_time');
        });
    }
};
