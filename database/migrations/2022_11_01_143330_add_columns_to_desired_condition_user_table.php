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
            $table->json('working_days')->nullable()->after('job_feature_ids');
            $table->char('start_working_hour', 4)->nullable()->after('working_days');
            $table->char('end_working_hour', 4)->nullable()->after('start_working_hour');
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
            $table->dropColumn(['working_days', 'start_working_hour', 'end_working_hour']);
        });
    }
};
