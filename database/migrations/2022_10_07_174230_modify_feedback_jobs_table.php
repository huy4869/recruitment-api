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
        Schema::table('feedback_jobs', function (Blueprint $table) {
            $table->dropColumn(['email', 'name', 'tel']);
            $table->unsignedBigInteger('job_posting_id')->after('feedback_type_ids');
            $table->string('desired_salary', 255)->after('type')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('feedback_jobs', function (Blueprint $table) {
            $table->dropColumn(['desired_salary', 'job_posting_id']);
            $table->string('email', 255)->nullable();
            $table->string('name', 255)->nullable();
            $table->string('tel', 15)->nullable();
        });
    }
};
