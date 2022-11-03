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
            $table->dropColumn(['start_working_hour', 'end_working_hour']);
            $table->char('start_working_time', 4)->nullable()->after('working_days');
            $table->char('end_working_time', 4)->nullable()->after('start_working_time');
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
            $table->dropColumn(['start_working_time', 'end_working_time']);
            $table->char('start_working_hour', 4)->nullable()->after('working_days');
            $table->char('end_working_hour', 4)->nullable()->after('start_working_hour');
        });
    }
};
